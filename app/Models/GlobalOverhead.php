<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class GlobalOverhead extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category',
        'description',
        'monthly_cost',
    ];

    protected $casts = [
        'id'           => 'string',
        'monthly_cost' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
