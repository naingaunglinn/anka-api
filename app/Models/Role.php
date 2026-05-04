<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

// Table is 'roles' (renamed from job_roles in ANKA.sql to match frontend expectation).
class Role extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'department_id',
        'title',
        'department',
        'rate',
    ];

    protected $casts = [
        'id'   => 'string',
        'rate' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'job_role_id');
    }

    public function estimationResources()
    {
        return $this->hasMany(EstimationResource::class, 'job_role_id');
    }
}
