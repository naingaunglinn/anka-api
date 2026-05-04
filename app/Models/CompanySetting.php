<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

// id is text DEFAULT 'singleton' — intentional exception to the UUID PK rule.
// Does NOT use HasUuids; PK is a plain string managed by the database default.
class CompanySetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'company_settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'overhead_percentage',
        'buffer_percentage',
        'yearly_fixed_cost',
        'employer_tax_percentage',
        'benefits_percentage',
    ];

    protected $casts = [
        'overhead_percentage'     => 'float',
        'buffer_percentage'       => 'float',
        'yearly_fixed_cost'       => 'float',
        'employer_tax_percentage' => 'float',
        'benefits_percentage'     => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
