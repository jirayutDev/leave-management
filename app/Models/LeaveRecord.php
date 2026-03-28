<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecord extends Model
{
    protected $fillable = [
        'employee_id', 'leave_date', 'leave_type', 'note', 'imported_from',
    ];

    protected $casts = [
        'leave_date' => 'date',
    ];

    public static array $typeLabels = [
        'V' => 'Vacation',
        'S' => 'Sick',
        'P' => 'Personal',
        'D' => 'Disability',
        'U' => 'Unpaid',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->leave_type] ?? $this->leave_type;
    }
}
