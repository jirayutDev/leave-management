<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'employee_code', 'name', 'email', 'department', 'position',
        'base_salary', 'allowance', 'hire_date', 'status',
    ];

    protected $casts = [
        'hire_date'   => 'date',
        'base_salary' => 'decimal:2',
        'allowance'   => 'decimal:2',
    ];

    public function leaveRecords(): HasMany
    {
        return $this->hasMany(LeaveRecord::class);
    }

    public function leaveQuotas(): HasMany
    {
        return $this->hasMany(LeaveQuota::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function quotaForYear(int $year): ?LeaveQuota
    {
        return $this->leaveQuotas()->where('year', $year)->first();
    }

    /** Used days of a leave type for a given month/year */
    public function usedLeaveInMonth(string $type, int $month, int $year): int
    {
        return $this->leaveRecords()
            ->whereMonth('leave_date', $month)
            ->whereYear('leave_date', $year)
            ->where('leave_type', $type)
            ->count();
    }

    /** Used days of a leave type for a given year */
    public function usedLeaveInYear(string $type, int $year): int
    {
        return $this->leaveRecords()
            ->whereYear('leave_date', $year)
            ->where('leave_type', $type)
            ->count();
    }
}
