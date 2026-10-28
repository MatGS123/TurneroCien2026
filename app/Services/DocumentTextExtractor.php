<?php

namespace App\Services;

use App\Models\Estudio;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class DocumentTextExtractor
{
    private const MAX_FILE_BYTES = 10_485_760;
    private const MAX_TEXT_CHARS = 100_000;

    /**
     * Extract document text without accepting a user-provided filesystem path.
     * The path always comes from an authorized Estudio record.
     *
     * @return array{text: string, pages: int|null, extension: string}
     */
    public function extract(Estudio $estudio): array
    {
        $relativePath = (string) $estudio->archivo_pdf;
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($relativePath);
        $root = realpath($disk->path(''));
        $realPath = realpath($absolutePath);

        if (! $root || ! $realPath || ! str_starts_with($realPath, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('La ruta del documento no es válida.');
        }

        if (! is_file($realPath) || ! is_readable($realPath)) {
            throw new RuntimeException('El documento no está disponible.');
        }

        if (filesize($realPath) > self::MAX_FILE_BYTES) {
            throw new RuntimeException('El documento supera el límite de 10 MB.');
        }

        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $result = match ($extension) {
            'pdf' => $this->extractPdf($realPath),
            'docx' => ['text' => $this->extractOpenXml($realPath, 'word/document.xml'), 'pages' => null],
            'doc' => ['text' => $this->extractWithLibreOffice($realPath), 'pages' => null],
            'odt' => ['text' => $this->extractOpenXml($realPath, 'content.xml'), 'pages' => null],
            'txt', 'md', 'csv' => ['text' => (string) file_get_contents($realPath), 'pages' => null],
            'rtf' => ['text' => $this->extractRtf($realPath), 'pages' => null],
            'png', 'jpg', 'jpeg', 'tif', 'tiff', 'webp' => ['text' => $this->extractImage($realPath), 'pages' => null],
            default => throw new RuntimeException('Tipo de documento no compatible.'),
        };

        $text = $this->normalize($result['text']);

        if ($text === '') {
            throw new RuntimeException('No se pudo extraer texto legible del documento.');
        }

        return [
            'text' => function_exists('mb_substr')
                ? mb_substr($text, 0, self::MAX_TEXT_CHARS)
                : substr($text, 0, self::MAX_TEXT_CHARS),
            'pages' => $result['pages'],
            'extension' => $extension,
        ];
    }

    private function extractPdf(string $path): array
    {
        $info = new Process(['pdfinfo', $path]);
        $info->setTimeout(20);
        $info->run();
        $pages = null;

        if ($info->isSuccessful() && preg_match('/^Pages:\s+(\d+)/mi', $info->getOutput(), $matches)) {
            $pages = (int) $matches[1];
        }

        $text = '';

        if ($pages && $pages <= 50) {
            for ($page = 1; $page <= $pages; $page++) {
                $command = new Process(['pdftotext', '-layout', '-f', (string) $page, '-l', (string) $page, '-enc', 'UTF-8', $path, '-']);
                $command->setTimeout(45);
                $command->run();

                if (! $command->isSuccessful()) {
                    throw new RuntimeException('No se pudo leer el PDF.');
                }

                $text .= "[Página {$page}]\n" . $command->getOutput() . "\n\n";
            }
        } else {
            $command = new Process(['pdftotext', '-layout', '-enc', 'UTF-8', $path, '-']);
            $command->setTimeout(45);
            $command->run();

            if (! $command->isSuccessful()) {
                throw new RuntimeException('No se pudo leer el PDF.');
            }

            $text = $command->getOutput();
        }

        if (trim($text) === '') {
            $text = $this->extractScannedPdf($path);
        }

        return ['text' => $text, 'pages' => $pages];
    }

    private function extractOpenXml(string $path, string $entry): string
    {
        $process = new Process(['unzip', '-p', $path, $entry]);
        $process->setTimeout(20);
        $process->run();

        if (! $process->isSuccessful() || trim($process->getOutput()) === '') {
            throw new RuntimeException('No se encontró el contenido textual del documento.');
        }

        $xml = $process->getOutput();

        $xml = preg_replace('/<w:tab\s*\/?\s*>/i', "\t", $xml) ?? $xml;
        $xml = preg_replace('/<w:(?:br|cr)\s*\/?\s*>/i', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<text:line-break\s*\/?\s*>/i', "\n", $xml) ?? $xml;

        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractRtf(string $path): string
    {
        $contents = (string) file_get_contents($path);
        $contents = preg_replace('/\\par[d]?\s*/i', "\n", $contents) ?? $contents;
        $contents = preg_replace("/\\\\'[0-9a-f]{2}/i", '', $contents) ?? $contents;
        $contents = preg_replace('/\\[a-z]+-?\d*\s?/i', '', $contents) ?? $contents;

        return trim(str_replace(['{', '}'], '', $contents));
    }

    private function extractImage(string $path): string
    {
        $process = new Process(['tesseract', $path, 'stdout', '-l', 'spa+eng']);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('No se pudo aplicar OCR a la imagen.');
        }

        return $process->getOutput();
    }

    private function extractScannedPdf(string $path): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cien-consultant-ocr-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700, true);

        try {
            $render = new Process(['pdftoppm', '-jpeg', '-r', '150', $path, $directory . DIRECTORY_SEPARATOR . 'page']);
            $render->setTimeout(90);
            $render->run();

            if (! $render->isSuccessful()) {
                throw new RuntimeException('No se pudo preparar el PDF escaneado para OCR.');
            }

            $pages = glob($directory . DIRECTORY_SEPARATOR . 'page-*.jpg') ?: [];
            sort($pages, SORT_NATURAL);
            $text = [];

            foreach ($pages as $index => $page) {
                $ocr = new Process(['tesseract', $page, 'stdout', '-l', 'spa+eng']);
                $ocr->setTimeout(60);
                $ocr->run();

                if ($ocr->isSuccessful() && trim($ocr->getOutput()) !== '') {
                    $text[] = '[Página ' . ($index + 1) . "]\n" . $ocr->getOutput();
                }
            }

            return implode("\n\n", $text);
        } finally {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($directory);
        }
    }

    private function extractWithLibreOffice(string $path): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cien-consultant-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700, true);

        try {
            $process = new Process(['libreoffice', '--headless', '--convert-to', 'txt:Text', '--outdir', $directory, $path]);
            $process->setTimeout(60);
            $process->run();

            $converted = $directory . DIRECTORY_SEPARATOR . pathinfo($path, PATHINFO_FILENAME) . '.txt';

            if (! $process->isSuccessful() || ! is_readable($converted)) {
                throw new RuntimeException('No se pudo convertir el documento DOC.');
            }

            return (string) file_get_contents($converted);
        } finally {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($directory);
        }
    }

    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
