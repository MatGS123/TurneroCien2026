public function up(): void
{
    Schema::create('coseguros', function (Blueprint $table) {
        $table->id();
        $table->foreignId('obra_social_id')->constrained('obras_sociales')->cascadeOnDelete();
        $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
        $table->decimal('monto', 10, 2)->default(0);
        $table->boolean('activo')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('coseguros');
}
