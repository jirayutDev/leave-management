<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveQuota extends Model
{
    protected $fillable = [
        'employee_id', 'year', 'vacation', 'sick', 'personal', 'disability', 'unpaid',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** Return quota by leave type key (V, S, P, D, U) */
    public function getByType(string $type): int
    {
        return match (strtoupper($type)) {
            'V' => $this->vacation,
            'S' => $this->sick,
            'P' => $this->personal,
            'D' => $this->disability,
            'U' => $this->unpaid,
            default => 0,
        };
    }

    /** Adjust quota by type */
    public function adjustByType(string $type, int $delta): void
    {
        $map = ['V' => 'vacation', 'S' => 'sick', 'P' => 'personal', 'D' => 'disability', 'U' => 'unpaid'];
        $col = $map[strtoupper($type)] ?? null;
        if ($col) {
            $this->increment($col, $delta);
        }
    }
}
