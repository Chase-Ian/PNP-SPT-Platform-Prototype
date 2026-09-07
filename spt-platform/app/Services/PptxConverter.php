<?php
// app/Services/PptxTextExtractor.php
namespace App\Services;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;

class PptxTextExtractor
{
    public function extract(string $filePath): array
    {
        $presentation = IOFactory::load($filePath);
        $blocks = [];

        foreach ($presentation->getAllSlides() as $slide) {
            $bulletItems = [];
            $isFirstTextShape = true;

            foreach ($slide->getShapeCollection() as $shape) {
                if (! $shape instanceof RichText) {
                    continue;
                }

                $text = trim($shape->getPlainText());
                if ($text === '') {
                    continue;
                }

                if ($isFirstTextShape) {
                    // First text shape on a slide is treated as the slide's heading (its title placeholder)
                    $blocks[] = ['type' => 'heading', 'text' => $text];
                    $isFirstTextShape = false;
                } else {
                    // Remaining lines become bullet list items
                    foreach (explode("\n", $text) as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            $bulletItems[] = ['bold' => '', 'text' => $line];
                        }
                    }
                }
            }

            if (! empty($bulletItems)) {
                $blocks[] = ['type' => 'bullet_list', 'items' => $bulletItems];
            }
        }

        return $blocks;
    }
}