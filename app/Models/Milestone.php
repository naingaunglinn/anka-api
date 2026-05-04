<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Milestone extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'name',
        'due_date',
        'amount',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'id'           => 'string',
        'due_date'     => 'date',
        'amount'       => 'float',
        'completed_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
