<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            // Nullable until the professional confirms the draft. The appointment
            // is kept in the encrypted context only while the draft is pending.
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Employee::class)->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('queued')->index();
            $table->string('audio_path')->nullable();
            $table->text('context')->nullable();
            $table->longText('transcription')->nullable();
            $table->longText('organized_content')->nullable();
            $table->string('txt_path')->nullable();
            $table->string('docx_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('transcribed_at')->nullable();
            $table->timestamp('organized_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
