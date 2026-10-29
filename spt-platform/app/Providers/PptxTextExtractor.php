<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use ZipArchive;

/**
 * Extracts TEXT ONLY from a .pptx file and maps it to the lesson block format
 * used by the lesson editor:
 *
 *   ['type' => 'heading',     'text'  => '...']
 *   ['type' => 'paragraph',   'text'  => '...']
 *   ['type' => 'bullet_list', 'items' => [['bold' => '', 'text' => '...'], ...]]
 *
 * Why not phpoffice/phppresentation: it loads the whole deck (images included)
 * into memory. This reads the .pptx as a zip and opens only the small slide XML
 * files, never ppt/media/*, so cost stays tiny even for very large decks.
 *
 * Safety: slide count, per-slide size, total size and block counts are capped;
 * XML is parsed with network access disabled and entities NOT substituted.
 */
class PptxTextExtractor
{
    private const MAX_SLIDES = 200;
    private const MAX_SLIDE_XML_BYTES = 2 * 1024 * 1024;   // per slide, actual bytes read
    private const MAX_TOTAL_XML_BYTES = 20 * 1024 * 1024;  // all slides combined
    private const MAX_BLOCKS = 500;
    private const MAX_TEXT_CHARS = 5000;                   // per paragraph / bullet / heading

    private const NS_A = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    private const NS_P = 'http://schemas.openxmlformats.org/presentationml/2006/main';
    private const NS_R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /** Placeholder types that are slide furniture, not lesson content. */
    private const SKIP_PLACEHOLDERS = ['dt', 'ftr', 'sldNum', 'hdr'];

    /**
     * @return array<int, array<string, mixed>>
     * @throws InvalidArgumentException when the file is not a readable .pptx
     */
    public function extract(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('That file is not a valid PPTX (could not open as a zip archive).');
        }

        try {
            // Real marker check — does not rely on the MIME type, which finfo
            // misreports as application/octet-stream for some PPTX files.
            if ($zip->locateName('ppt/presentation.xml') === false) {
                throw new InvalidArgumentException('That file is not a PowerPoint (.pptx) presentation.');
            }

            $slidePaths = $this->orderedSlidePaths($zip);
            if (count($slidePaths) === 0) {
                throw new InvalidArgumentException('No slides were found in that presentation.');
            }

            $blocks = [];
            $totalBytes = 0;

            foreach (array_slice($slidePaths, 0, self::MAX_SLIDES) as $slidePath) {
                $xml = $this->readEntry($zip, $slidePath, self::MAX_SLIDE_XML_BYTES);
                if ($xml === null) {
                    continue; // missing or oversized slide: skip rather than fail the whole import
                }

                $totalBytes += strlen($xml);
                if ($totalBytes > self::MAX_TOTAL_XML_BYTES) {
                    break;
                }

                foreach ($this->blocksFromSlide($xml) as $block) {
                    $blocks[] = $block;
                    if (count($blocks) >= self::MAX_BLOCKS) {
                        break 2;
                    }
                }
            }

            return $blocks;
        } finally {
            $zip->close();
        }
    }

    /**
     * Slide files in DISPLAY order (presentation.xml sldIdLst -> rels), because
     * slideN.xml numbering does not always match the order slides are shown in.
     * Falls back to numeric filename order if the order can't be resolved.
     *
     * @return string[]
     */
    private function orderedSlidePaths(ZipArchive $zip): array
    {
        $numeric = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $m)) {
                $numeric[(int) $m[1]] = $name;
            }
        }
        ksort($numeric);
        $fallback = array_values($numeric);

        $presXml = $this->readEntry($zip, 'ppt/presentation.xml', self::MAX_SLIDE_XML_BYTES);
        $relsXml = $this->readEntry($zip, 'ppt/_rels/presentation.xml.rels', self::MAX_SLIDE_XML_BYTES);
        if ($presXml === null || $relsXml === null) {
            return $fallback;
        }

        $pres = $this->loadXml($presXml);
        $rels = $this->loadXml($relsXml);
        if (! $pres || ! $rels) {
            return $fallback;
        }

        $targets = [];
        $relXp = new DOMXPath($rels);
        $relXp->registerNamespace('rel', self::NS_REL);
        foreach ($relXp->query('//rel:Relationship') as $rel) {
            /** @var DOMElement $rel */
            $targets[$rel->getAttribute('Id')] = $rel->getAttribute('Target');
        }

        $presXp = new DOMXPath($pres);
        $presXp->registerNamespace('p', self::NS_P);
        $ordered = [];
        foreach ($presXp->query('//p:sldIdLst/p:sldId') as $sldId) {
            /** @var DOMElement $sldId */
            $rid = $sldId->getAttributeNS(self::NS_R, 'id');
            $target = $targets[$rid] ?? null;
            if ($target === null) {
                continue;
            }
            // Targets are relative to ppt/ (e.g. "slides/slide1.xml") or absolute ("/ppt/slides/slide1.xml").
            $full = str_starts_with($target, '/') ? ltrim($target, '/') : 'ppt/' . $target;
            if (preg_match('#^ppt/slides/slide\d+\.xml$#', $full) && $zip->locateName($full) !== false) {
                $ordered[] = $full;
            }
        }

        return count($ordered) > 0 ? $ordered : $fallback;
    }

    /**
     * Bounded read of a zip entry. Reads at most $maxBytes + 1 from the real
     * stream, so a lying zip header (zip bomb) can never inflate memory use.
     */
    private function readEntry(ZipArchive $zip, string $name, int $maxBytes): ?string
    {
        $stream = $zip->getStream($name);
        if ($stream === false) {
            return null;
        }

        $data = stream_get_contents($stream, $maxBytes + 1);
        fclose($stream);

        if ($data === false || strlen($data) > $maxBytes) {
            return null;
        }

        return $data;
    }

    private function loadXml(string $xml): ?DOMDocument
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // LIBXML_NONET: no network fetches. No LIBXML_NOENT: entities are never substituted.
        $ok = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok ? $dom : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function blocksFromSlide(string $xml): array
    {
        $dom = $this->loadXml($xml);
        if (! $dom) {
            return [];
        }

        $xp = new DOMXPath($dom);
        $xp->registerNamespace('a', self::NS_A);
        $xp->registerNamespace('p', self::NS_P);

        $blocks = [];
        $bulletRun = []; // consecutive bullet items, flushed into one bullet_list block

        $flush = function () use (&$blocks, &$bulletRun) {
            if ($bulletRun) {
                $blocks[] = ['type' => 'bullet_list', 'items' => $bulletRun];
                $bulletRun = [];
            }
        };

        // Shapes and tables in document (z-order) order; // handles shapes nested in groups.
        $nodes = $xp->query('//p:cSld/p:spTree//*[self::p:sp or self::a:tbl]');
        foreach ($nodes as $node) {
            /** @var DOMElement $node */
            if ($node->localName === 'tbl') {
                foreach ($xp->query('.//a:tr', $node) as $row) {
                    $cells = [];
                    foreach ($xp->query('./a:tc', $row) as $cell) {
                        $t = $this->cleanText($this->plainText($xp, $cell));
                        if ($t !== '') {
                            $cells[] = $t;
                        }
                    }
                    if ($cells) {
                        $bulletRun[] = ['bold' => '', 'text' => $this->limit(implode(' | ', $cells))];
                    }
                }
                continue;
            }

            $ph = $xp->query('./p:nvSpPr/p:nvPr/p:ph', $node)->item(0);
            $phType = $ph instanceof DOMElement ? ($ph->getAttribute('type') ?: 'body') : null;

            if ($phType !== null && in_array($phType, self::SKIP_PLACEHOLDERS, true)) {
                continue;
            }

            $isTitle = in_array($phType, ['title', 'ctrTitle'], true);
            $isBodyPlaceholder = $phType !== null && ! $isTitle && $phType !== 'subTitle';

            foreach ($xp->query('./p:txBody/a:p', $node) as $para) {
                /** @var DOMElement $para */
                [$lead, $rest, $full] = $this->paragraphParts($xp, $para);
                if ($full === '') {
                    continue;
                }

                if ($isTitle) {
                    $flush();
                    $blocks[] = ['type' => 'heading', 'text' => $this->limit($full)];
                    continue;
                }

                $hasBullet = $xp->query('./a:pPr/a:buChar | ./a:pPr/a:buAutoNum', $para)->length > 0;
                $noBullet = $xp->query('./a:pPr/a:buNone', $para)->length > 0;
                $asBullet = $hasBullet || ($isBodyPlaceholder && ! $noBullet);

                if ($asBullet) {
                    $bulletRun[] = ['bold' => $this->limit($lead), 'text' => $this->limit($lead !== '' ? $rest : $full)];
                } else {
                    $flush();
                    $blocks[] = ['type' => 'paragraph', 'text' => $this->limit($full)];
                }
            }

            $flush();
        }

        $flush();

        return $blocks;
    }

    /**
     * Returns [boldLeadIn, remainder, fullText]. A bold lead-in is only reported
     * when the paragraph STARTS with bold run(s) and is followed by non-bold text
     * (matches the editor's "Bold lead-in" + "Item text" fields).
     *
     * @return array{0:string,1:string,2:string}
     */
    private function paragraphParts(DOMXPath $xp, DOMElement $para): array
    {
        $runs = []; // [text, isBold]
        foreach ($xp->query('./a:r | ./a:br | ./a:fld', $para) as $el) {
            /** @var DOMElement $el */
            if ($el->localName === 'br') {
                $runs[] = [' ', false];
                continue;
            }
            $t = '';
            foreach ($xp->query('./a:t', $el) as $tn) {
                $t .= $tn->textContent;
            }
            $rPr = $xp->query('./a:rPr', $el)->item(0);
            $bold = $rPr instanceof DOMElement && in_array($rPr->getAttribute('b'), ['1', 'true'], true);
            $runs[] = [$t, $bold];
        }

        $full = $this->cleanText(implode('', array_column($runs, 0)));

        $lead = '';
        $rest = '';
        $i = 0;
        while ($i < count($runs) && $runs[$i][1]) {
            $lead .= $runs[$i][0];
            $i++;
        }
        for (; $i < count($runs); $i++) {
            $rest .= $runs[$i][0];
        }
        $lead = $this->cleanText($lead);
        $rest = $this->cleanText($rest);

        if ($lead === '' || $rest === '') {
            return ['', $full, $full]; // all-bold or no-bold paragraph: no lead-in split
        }

        return [$lead, ltrim($rest, " :–—-"), $full];
    }

    private function plainText(DOMXPath $xp, DOMElement $context): string
    {
        $out = [];
        foreach ($xp->query('.//a:t', $context) as $t) {
            $out[] = $t->textContent;
        }

        return implode(' ', $out);
    }

    private function cleanText(string $text): string
    {
        // Collapse whitespace (incl. vertical tabs PowerPoint uses for soft breaks).
        return trim(preg_replace('/[\s\x{00A0}\x{000B}]+/u', ' ', $text) ?? '');
    }

    private function limit(string $text): string
    {
        return mb_substr($text, 0, self::MAX_TEXT_CHARS);
    }
}
