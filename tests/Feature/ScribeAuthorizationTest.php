<?php

use App\Models\ClinicalNote;
use App\Models\Employee;
use App\Models\User;
use App\Policies\ClinicalNotePolicy;

/**
 * Pruebas de la Policy (sin base de datos). Cubren las reglas de acceso reales:
 * dueño vs. otro profesional vs. admin vs. usuario sin empleado, y qué estado habilita cada acción.
 * Las pruebas HTTP completas requieren factories/seeders del proyecto y se mantienen fuera de esta batería rápida.
 */
function scribeUser(int $id, ?int $employeeId, bool $isEmployee = true): User
{
    $user = Mockery::mock(User::class)->makePartial();
    $user->id = $id;
    $user->setRelation('employee', $employeeId ? new Employee(['id' => $employeeId, 'user_id' => $id]) : null);
    $user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
    $user->shouldReceive('hasRole')->with('empleado')->andReturn($isEmployee);

    return $user;
}

function scribeNote(string $status, int $userId = 10, ?int $employeeId = 7): ClinicalNote
{
    return new ClinicalNote(['user_id' => $userId, 'employee_id' => $employeeId, 'status' => $status]);
}

it('el profesional dueño ve su nota', function () {
    expect((new ClinicalNotePolicy)->view(scribeUser(10, 7), scribeNote('transcribed')))->toBeTrue();
});

it('otro profesional no puede ver, editar, confirmar, exportar ni descargar la nota ajena', function () {
    $other = scribeUser(11, 8);
    $policy = new ClinicalNotePolicy;

    foreach (['transcribed', 'organized', 'confirmed'] as $status) {
        $note = scribeNote($status);
        expect($policy->view($other, $note))->toBeFalse()
            ->and($policy->update($other, $note))->toBeFalse()
            ->and($policy->confirm($other, $note))->toBeFalse()
            ->and($policy->export($other, $note))->toBeFalse()
            ->and($policy->download($other, $note))->toBeFalse();
    }
});

it('superadmin y usuarios sin rol empleado o sin empleado asociado reciben acceso denegado', function () {
    $policy = new ClinicalNotePolicy;
    $note = scribeNote('confirmed');

    $admin = scribeUser(1, null, false);
    $noEmployeeRecord = scribeUser(10, null, true);

    expect($policy->view($admin, $note))->toBeFalse()
        ->and($policy->download($admin, $note))->toBeFalse()
        ->and($policy->view($noEmployeeRecord, $note))->toBeFalse();
});

it('la misma persona con un employee distinto al de la nota queda rechazada', function () {
    $sameUserOtherEmployee = scribeUser(10, 99);

    expect((new ClinicalNotePolicy)->view($sameUserOtherEmployee, scribeNote('confirmed', 10, 7)))->toBeFalse();
});

it('cada acción solo se habilita en el estado correcto', function () {
    $owner = scribeUser(10, 7);
    $policy = new ClinicalNotePolicy;

    expect($policy->update($owner, scribeNote('queued')))->toBeFalse()
        ->and($policy->update($owner, scribeNote('transcribed')))->toBeTrue()
        ->and($policy->update($owner, scribeNote('confirmed')))->toBeFalse()
        ->and($policy->confirm($owner, scribeNote('transcribed')))->toBeFalse()
        ->and($policy->confirm($owner, scribeNote('organized')))->toBeTrue()
        ->and($policy->export($owner, scribeNote('organized')))->toBeFalse()
        ->and($policy->export($owner, scribeNote('confirmed')))->toBeTrue()
        ->and($policy->download($owner, scribeNote('organized')))->toBeFalse()
        ->and($policy->download($owner, scribeNote('confirmed')))->toBeTrue();
});

it('una nota sin employee_id asignado sigue siendo solo del usuario que la creó', function () {
    $policy = new ClinicalNotePolicy;
    $note = scribeNote('transcribed', 10, null);

    expect($policy->view(scribeUser(10, 7), $note))->toBeTrue()
        ->and($policy->view(scribeUser(11, 8), $note))->toBeFalse();
});
