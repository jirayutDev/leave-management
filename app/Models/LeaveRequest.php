<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type', 'start_date', 'end_date', 'days', 'reason', 'attachment',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** Count working days (Mon–Fri) between start and end inclusive */
    public static function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $days    = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        return max(1, $days);
    }

    /** Create LeaveRecord rows for each working day in the range */
    public function createLeaveRecords(): int
    {
        $created = 0;
        $current = $this->start_date->copy();
        while ($current->lte($this->end_date)) {
            if (!$current->isWeekend()) {
                LeaveRecord::firstOrCreate(
                    ['employee_id' => $this->employee_id, 'leave_date' => $current->toDateString()],
                    ['leave_type' => $this->leave_type, 'note' => mb_substr($this->reason, 0, 100)]
                );
                $created++;
            }
            $current->addDay();
        }
        return $created;
    }
}
