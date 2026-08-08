public function up(): void
{
    Schema::table('obras_sociales', function (Blueprint $table) {
        $table->string('plan', 100)->nullable()->after('nombre');
        $table->string('prestadora', 150)->nullable()->after('plan');
        $table->boolean('activo')->default(true)->after('prestadora');
    });
}

public function down(): void
{
    Schema::table('obras_sociales', function (Blueprint $table) {
        $table->dropColumn(['plan', 'prestadora', 'activo']);
    });
}
