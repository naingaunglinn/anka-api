<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

// cost_per_hour is a PostgreSQL GENERATED column — never add it to $fillable.
class Employee extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'department_id',
        'job_role_id',
        'name',
        'role',
        'role_name',
        'capacity_role',
        'monthly_salary',
        'workable_hours',
        'status',
    ];

    protected $casts = [
        'id'             => 'string',
        'monthly_salary' => 'float',
        'workable_hours' => 'integer',
        'cost_per_hour'  => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobRole()
    {
        return $this->belongsTo(Role::class, 'job_role_id');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function hardAssignments()
    {
        return $this->hasMany(DealHardAssignment::class);
    }
}
