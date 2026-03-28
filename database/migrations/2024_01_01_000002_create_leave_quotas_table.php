<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->integer('vacation')->default(0);    // V
            $table->integer('sick')->default(0);        // S
            $table->integer('personal')->default(0);    // P
            $table->integer('disability')->default(0);  // D
            $table->integer('unpaid')->default(0);      // U
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_quotas');
    }
};
