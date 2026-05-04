<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

// Rows are deleted and re-inserted as a complete set on every deal estimation update.
class EstimationResource extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'job_role_id',
        'role_id',
        'feature_name',
        'hours',
    ];

    protected $casts = [
        'id'    => 'string',
        'hours' => 'float',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'job_role_id');
    }
}
