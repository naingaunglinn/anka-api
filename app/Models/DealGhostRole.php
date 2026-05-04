<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class DealGhostRole extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'deal_ghost_roles';

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'role_type',
        'quantity',
        'months',
        'avg_monthly_salary',
    ];

    protected $casts = [
        'id'                 => 'string',
        'quantity'           => 'integer',
        'months'             => 'integer',
        'avg_monthly_salary' => 'float',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
