<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class DealHardAssignment extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'deal_hard_assignments';

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'employee_id',
        'allocated_hours',
    ];

    protected $casts = [
        'id'              => 'string',
        'allocated_hours' => 'float',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
