<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('leave_type', ['V', 'S', 'P', 'D', 'U']);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days')->default(1); // จำนวนวันลา (ไม่นับวันหยุด)
            $table->text('reason');                           // สาเหตุการลา
            $table->string('attachment')->nullable();         // ไฟล์แนบ (เช่น ใบรับรองแพทย์)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();           // หมายเหตุผู้อนุมัติ
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
