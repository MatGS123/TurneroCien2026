<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Employee;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $rename = [
            'admin' => 'superadmin',
            'moderator' => 'secretarias',
            'employee' => 'empleado',
        ];

        foreach ($rename as $old => $new) {
            $oldRole = Role::where('name', $old)->where('guard_name', $guard)->first();
            $newRole = Role::firstOrCreate(['name' => $new, 'guard_name' => $guard]);

            if ($oldRole && $oldRole->id !== $newRole->id) {
                $newRole->syncPermissions($oldRole->permissions);
                foreach ($oldRole->users as $user) {
                    $user->assignRole($newRole);
                    $user->removeRole($oldRole);
                }
                $oldRole->delete();
            }
        }

        $permissions = [
            'ai.view',
            'studies.view',
            'profile.view',
            'settings.edit',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $superadmin = Role::findByName('superadmin', $guard);
        $secretarias = Role::findByName('secretarias', $guard);
        $empleado = Role::findByName('empleado', $guard);

        $superadmin->syncPermissions(Permission::where('guard_name', $guard)->get());
        $secretarias->syncPermissions(Permission::where('guard_name', $guard)
            ->whereNotIn('name', ['settings.edit', 'ai.view', 'studies.view', 'profile.view'])
            ->get());
        $empleado->syncPermissions(Permission::where('guard_name', $guard)
            ->whereIn('name', ['appointments.view', 'ai.view', 'studies.view', 'profile.view'])
            ->get());

        // subscriber deja de ser un rol operativo. Sus usuarios conservan sus cuentas,
        // pero ya no reciben permisos ni aparecen como rol administrable.
        $subscriber = Role::where('name', 'subscriber')->where('guard_name', $guard)->first();
        if ($subscriber) {
            foreach ($subscriber->users as $user) {
                $user->removeRole($subscriber);
            }
            $subscriber->delete();
        }

        // El superadmin no se considera empleado. Si el registro histórico no tiene turnos,
        // se elimina; si tiene turnos, se conserva para no romper sus claves foráneas históricas.
        User::query()->role('superadmin')->with('employee.appointments')->get()->each(function ($user) {
            if ($user->employee && $user->employee->appointments->isEmpty()) {
                $user->employee->delete();
            }
        });
    }

    public function down(): void
    {
        // No se revierten nombres de roles ni asignaciones para evitar reintroducir
        // permisos obsoletos o convertir usuarios comunes en administradores.
    }
};
