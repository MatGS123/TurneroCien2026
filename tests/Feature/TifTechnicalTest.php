<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

it('usa una versión de PHP compatible con CIEN', function () {
    expect(PHP_VERSION_ID)->toBeGreaterThanOrEqual(80300);
});

it('conserva los archivos principales de la Suite de IA y Scribe', function () {
    foreach ([
        'app/Ai/Agents/Researcher.php',
        'app/Ai/Agents/Consultant.php',
        'app/Ai/Agents/Scribe.php',
        'app/Http/Controllers/AiSuiteController.php',
        'app/Http/Controllers/ScribeController.php',
        'resources/views/backend/ai-suite/index.blade.php',
        'resources/views/backend/ai-suite/scribe.blade.php',
    ] as $file) {
        expect(File::exists(base_path($file)))->toBeTrue("No existe {$file}");
    }
});

it('expone las rutas principales del sistema y de la Suite de IA', function () {
    foreach ([
        'dashboard',
        'appointments',
        'user.index',
        'estudios.index',
        'ai.suite.index',
        'ai.researcher',
        'ai.consultant',
        'ai.scribe',
    ] as $routeName) {
        expect(Route::has($routeName))->toBeTrue("No existe la ruta {$routeName}");
    }
});

it('tiene configurado el flujo de Scribe y los tres roles de CIEN', function () {
    expect(config('ai.scribe_provider'))->toBe('groq')
        ->and(config('ai.scribe_max_duration_seconds'))->toBeInt()
        ->and(config('ai.scribe_max_duration_seconds'))->toBeLessThanOrEqual(600);

    $migration = File::get(database_path('migrations/2026_11_16_000001_migrate_roles_to_cien.php'));
    expect($migration)->toContain("'superadmin'")
        ->and($migration)->toContain("'secretarias'")
        ->and($migration)->toContain("'empleado'");
});

it('mantiene las migraciones de notas clínicas y email opcional', function () {
    expect(File::exists(database_path('migrations/2026_11_16_000002_make_emails_nullable.php')))->toBeTrue();

    $usersMigration = File::get(database_path('migrations/0001_01_01_000000_create_users_table.php'));
    $appointmentsMigration = File::get(database_path('migrations/2024_12_01_033013_create_appointments_table.php'));

    expect($usersMigration)->toContain('nullable')
        ->and($appointmentsMigration)->toContain('nullable');
});

it('mantiene el idioma visible en español', function () {
    expect(config('app.locale'))->toBeIn(['es', 'es_AR', 'es_ES']);
    expect(File::exists(public_path('vendor/datatables/i18n/es-ES.json')))->toBeTrue();
});
