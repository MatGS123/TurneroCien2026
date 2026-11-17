<?php

namespace App\Policies;

use App\Models\ClinicalNote;
use App\Models\User;

class ClinicalNotePolicy
{
    public function view(User $user, ClinicalNote $note): bool
    {
        return $this->ownsNote($user, $note);
    }

    public function update(User $user, ClinicalNote $note): bool
    {
        return $this->ownsNote($user, $note)
            && in_array($note->status, ['transcribed', 'organized'], true);
    }

    public function confirm(User $user, ClinicalNote $note): bool
    {
        return $this->ownsNote($user, $note)
            && $note->status === 'organized';
    }

    /** Reintentar la generación de TXT/DOCX/PDF de una nota ya confirmada. */
    public function export(User $user, ClinicalNote $note): bool
    {
        return $this->ownsNote($user, $note)
            && $note->status === 'confirmed';
    }

    public function download(User $user, ClinicalNote $note): bool
    {
        return $this->ownsNote($user, $note)
            && $note->status === 'confirmed';
    }

    private function ownsNote(User $user, ClinicalNote $note): bool
    {
        if ($user->hasRole('superadmin')) {
            return (int) $note->user_id === (int) $user->id;
        }

        return $user->hasRole('empleado')
            && $user->employee !== null
            && (int) $note->user_id === (int) $user->id
            && ($note->employee_id === null || (int) $note->employee_id === (int) $user->employee->id);
    }
}
