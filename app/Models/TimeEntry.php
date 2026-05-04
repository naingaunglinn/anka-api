<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

// Approval increments projects.consumed_hours exactly once via lockForUpdate() in a transaction.
// Do not use DB triggers to maintain consumed_hours — application code owns this.
class TimeEntry extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'employee_id',
        'approved_by',
        'task',
        'date',
        'hours',
        'billable',
        'status',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'id'          => 'string',
        'date'        => 'date',
        'hours'       => 'float',
        'billable'    => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
