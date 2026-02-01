<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('requests')) {
            Schema::create('requests', function (Blueprint $table) {
                $table->id();
                $table->string('tracking_id')->unique();
                $table->string('student_name');
                $table->string('student_email');
                $table->string('university');
                $table->decimal('gpa', 4, 2)->nullable();
                $table->string('purpose')->nullable();

                $table->enum('status', ['Submitted', 'Under Review', 'Approved', 'Rejected', 'Archived', 'Needs Revision'])->default('Submitted');
                $table->text('rejection_reason')->nullable();

                // For generated letters
                $table->longText('letter_content')->nullable();
                $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();

                // Docs
                $table->string('document_path')->nullable();

                $table->string('verification_token')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('training_period')->nullable();
                $table->text('admin_message')->nullable();
                $table->string('content_option')->nullable();
                $table->longText('custom_content')->nullable(); // Encrypted
                $table->longText('form_data')->nullable(); // Encrypted JSON
                $table->date('deadline')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
