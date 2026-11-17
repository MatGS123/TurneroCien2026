<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('scribe:clean-audio', function () {
    $disk = Storage::disk(config('ai.scribe_audio_disk', 'local'));
    $cutoff = now()->subHours(config('ai.scribe_temp_audio_hours', 24))->timestamp;

    foreach ($disk->files('scribe/tmp') as $file) {
        if ($disk->lastModified($file) < $cutoff) {
            $disk->delete($file);
        }
    }

    $this->info('Audios temporales de Scribe limpiados.');
})->purpose('Eliminar audios temporales de Scribe');

Schedule::command('scribe:clean-audio')->dailyAt('03:00');
