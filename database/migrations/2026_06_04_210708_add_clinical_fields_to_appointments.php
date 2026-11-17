<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'obra_social_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('obra_social_id')->nullable()->after('obra_social')
                    ->constrained('obras_sociales')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('appointments', 'fecha_nacimiento')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->date('fecha_nacimiento')->nullable()->after('obra_social_id');
            });
        }
        if (! Schema::hasColumn('appointments', 'domicilio')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('domicilio')->nullable()->after('fecha_nacimiento');
            });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['obra_social_id']);
            $table->dropColumn(['obra_social_id', 'fecha_nacimiento', 'domicilio']);
        });
    }
};
