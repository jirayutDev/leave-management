<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('leave_date');
            $table->enum('leave_type', ['V', 'S', 'P', 'D', 'U']);
            $table->string('note')->nullable();
            $table->string('imported_from')->nullable(); // e.g. CSV filename
            $table->timestamps();

            $table->unique(['employee_id', 'leave_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_records');
    }
};
