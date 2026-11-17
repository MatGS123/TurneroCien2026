<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class AudioDurationValidator
{
    /** Margen para que un corte justo en 10:00 en el navegador no sea rechazado por décimas de segundo. */
    private const GRACE_SECONDS = 5;

    public function validate(string $path, int $maxSeconds): float
    {
        if (! $this->available()) {
            throw new RuntimeException('No se puede validar la duración del audio en el servidor porque ffprobe no está instalado.');
        }

        $seconds = $this->duration($path);

        if ($seconds <= 0 || $seconds > $maxSeconds + self::GRACE_SECONDS) {
            throw new RuntimeException('El audio debe durar como máximo '.intdiv($maxSeconds, 60).' minutos.');
        }

        return $seconds;
    }

    public function available(): bool
    {
        return Process::run(['ffprobe', '-version'])->successful();
    }

    private function duration(string $path): float
    {
        // 1) Camino normal: duración declarada en el contenedor (archivos subidos, wav, mp3, etc.).
        $result = Process::timeout(60)->run([
            'ffprobe', '-v', 'error', '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1', $path,
        ]);

        if ($result->successful() && is_numeric(trim($result->output()))) {
            return (float) trim($result->output());
        }

        // 2) Las grabaciones de MediaRecorder (webm) NO traen la duración en el encabezado:
        //    ffprobe devuelve "N/A". En ese caso se toma el timestamp del último paquete de audio.
        $result = Process::timeout(60)->run([
            'ffprobe', '-v', 'error', '-select_streams', 'a:0',
            '-show_entries', 'packet=pts_time',
            '-of', 'default=noprint_wrappers=1:nokey=1', $path,
        ]);

        if ($result->failed()) {
            throw new RuntimeException('No se pudo leer la duración del audio en el servidor.');
        }

        $max = 0.0;
        foreach (preg_split('/\R/', trim($result->output())) as $line) {
            $line = trim($line);
            if (is_numeric($line) && (float) $line > $max) {
                $max = (float) $line;
            }
        }

        if ($max <= 0) {
            throw new RuntimeException('No se pudo leer la duración del audio en el servidor.');
        }

        return $max;
    }
}
