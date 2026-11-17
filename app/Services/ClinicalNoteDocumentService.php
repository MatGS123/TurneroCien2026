<?php

namespace App\Services;

use App\Models\ClinicalNote;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use RuntimeException;
use Throwable;

class ClinicalNoteDocumentService
{
    /**
     * Genera TXT, DOCX y PDF. Todo o nada: primero arma los tres contenidos en memoria,
     * después los escribe y verifica; si algo falla borra lo que haya escrito y lanza excepción.
     *
     * @return array{txt:string,docx:string,pdf:string}
     */
    public function generate(ClinicalNote $note): array
    {
        if ($note->status !== 'confirmed' || blank($note->organized_content)) {
            throw new RuntimeException('Solo se pueden exportar notas confirmadas.');
        }

        $content = (string) $note->organized_content;
        $title = $note->title ?: 'Nota clínica';

        $files = [
            'txt' => $content,
            'docx' => $this->docx($title, $content),
            'pdf' => $this->pdf($title, $content),
        ];

        $disk = Storage::disk(config('ai.scribe_document_disk', 'local'));
        $disk->makeDirectory('scribe/documents');
        $base = 'scribe/documents/clinical-note-'.$note->id;
        $paths = [
            'txt' => $base.'.txt',
            'docx' => $base.'.docx',
            'pdf' => $base.'.pdf',
        ];

        try {
            foreach ($paths as $format => $path) {
                $contents = $files[$format];
                if ($contents === '' || $contents === false) {
                    throw new RuntimeException("El archivo {$format} quedó vacío.");
                }
                if (! $disk->put($path, $contents) || ! $disk->exists($path) || $disk->size($path) <= 0) {
                    throw new RuntimeException("No se pudo guardar el archivo {$format}.");
                }
            }
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                try {
                    $disk->delete($path);
                } catch (Throwable) {
                    // limpieza de mejor esfuerzo
                }
            }
            throw $exception;
        }

        return $paths;
    }

    private function docx(string $title, string $content): string
    {
        // Sin esto, un "&" o un "<" en la nota (ej. "<5 mg") genera un DOCX corrupto.
        Settings::setOutputEscapingEnabled(true);

        $word = new PhpWord();
        $section = $word->addSection();
        $section->addTitle($title, 1);
        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);
            if ($line === '') {
                $section->addText('');
                continue;
            }
            if (str_starts_with($line, '#')) {
                $section->addText(ltrim($line, '# '), ['bold' => true, 'size' => 13]);
            } else {
                $section->addText($line, ['size' => 11]);
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'scribe-docx-');
        try {
            IOFactory::createWriter($word, 'Word2007')->save($tmp);
            $bytes = file_get_contents($tmp);
        } finally {
            @unlink($tmp);
        }

        return $bytes === false ? '' : $bytes;
    }

    private function pdf(string $title, string $content): string
    {
        $html = '<!doctype html><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;line-height:1.5}h1{color:#1f4e79}p{margin:0 0 8px}</style><h1>'.e($title).'</h1>';
        $html .= '<div>'.nl2br(e($content), false).'</div>';

        $pdf = new Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        return (string) $pdf->output();
    }
}
