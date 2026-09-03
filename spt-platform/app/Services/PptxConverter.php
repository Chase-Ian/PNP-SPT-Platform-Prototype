<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleSlide;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class PptxConverter
{
    private const SOFFICE_PATH = 'C:\Program Files\LibreOffice\program\soffice.exe';
    private const GHOSTSCRIPT_PATH = 'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe';

    public function convert(Module $module): void
    {
        $sourcePath = Storage::path($module->file_path);
        $outputDir = storage_path("app/module_slides/{$module->id}");
        $profileDir = storage_path('app/lo_profile'); // dedicated writable profile, avoids %APPDATA% permission issues

        foreach ([$outputDir, $profileDir] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $profileUrl = 'file:///' . str_replace('\\', '/', $profileDir);

        $toPdf = new Process([
            self::SOFFICE_PATH,
            '--headless',
            '--norestore',
            '-env:UserInstallation=' . $profileUrl,
            '--convert-to', 'pdf',
            '--outdir', $outputDir,
            $sourcePath,
        ]);
        $toPdf->setTimeout(120);
        $toPdf->run();

        \Log::info('soffice output', [
            'exit_code' => $toPdf->getExitCode(),
            'stdout' => $toPdf->getOutput(),
            'stderr' => $toPdf->getErrorOutput(),
        ]);

        $pdfOutput = $outputDir . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';

        if (! $toPdf->isSuccessful() || ! file_exists($pdfOutput)) {
            throw new \RuntimeException('PPTX to PDF conversion failed. Exit code: ' . $toPdf->getExitCode() . '. Output: ' . $toPdf->getOutput() . '. Error: ' . $toPdf->getErrorOutput());
        }

        $pngPattern = $outputDir . DIRECTORY_SEPARATOR . 'slide-%03d.png';

        $toPng = new Process([
            self::GHOSTSCRIPT_PATH,
            '-dBATCH',
            '-dNOPAUSE',
            '-sDEVICE=png16m',
            '-r150',
            "-sOutputFile={$pngPattern}",
            $pdfOutput,
        ]);
        $toPng->setTimeout(120);
        $toPng->run();

        if (! $toPng->isSuccessful()) {
            throw new \RuntimeException('PDF to PNG conversion failed: ' . $toPng->getErrorOutput());
        }

        ModuleSlide::where('module_id', $module->id)->delete();

        $files = glob($outputDir . DIRECTORY_SEPARATOR . 'slide-*.png');
        sort($files);

        foreach ($files as $index => $filePath) {
            $filename = basename($filePath);
            $relativePath = "module_slides/{$module->id}/{$filename}";

            ModuleSlide::create([
                'module_id' => $module->id,
                'slide_number' => $index + 1,
                'image_path' => $relativePath,
            ]);
        }

        unlink($pdfOutput);
    }
}