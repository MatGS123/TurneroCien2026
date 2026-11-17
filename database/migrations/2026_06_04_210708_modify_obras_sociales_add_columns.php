<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('obras_sociales', 'plan')) {
            Schema::table('obras_sociales', function (Blueprint $table) {
                $table->string('plan', 100)->nullable()->after('nombre');
            });
        }
        if (! Schema::hasColumn('obras_sociales', 'prestadora')) {
            Schema::table('obras_sociales', function (Blueprint $table) {
                $table->string('prestadora', 150)->nullable()->after('plan');
            });
        }
        if (! Schema::hasColumn('obras_sociales', 'activo')) {
            Schema::table('obras_sociales', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('prestadora');
            });
        }
    }

    public function down(): void
    {
        Schema::table('obras_sociales', function (Blueprint $table) {
            $table->dropColumn(['plan', 'prestadora', 'activo']);
        });
    }
};
