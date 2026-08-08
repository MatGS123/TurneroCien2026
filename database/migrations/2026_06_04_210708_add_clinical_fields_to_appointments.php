public function up(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->foreignId('obra_social_id')->nullable()->after('obra_social')
        ->constrained('obras_sociales')->nullOnDelete();
        $table->date('fecha_nacimiento')->nullable()->after('obra_social_id');
        $table->string('domicilio')->nullable()->after('fecha_nacimiento');
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropForeign(['obra_social_id']);
        $table->dropColumn(['obra_social_id', 'fecha_nacimiento', 'domicilio']);
    });
}
