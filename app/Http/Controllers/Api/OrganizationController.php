<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\GlobalOverheadResource;
use App\Http\Resources\CompanySettingResource;
use App\Models\Department;
use App\Models\Role;
use App\Models\Employee;
use App\Models\GlobalOverhead;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    // ── Departments ───────────────────────────────────────────────────────────

    public function indexDepartments()
    {
        return DepartmentResource::collection(Department::orderBy('created_at')->get());
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'id'        => 'sometimes|uuid',
            'name'      => 'required|string|max:255',
            'manager'   => 'required|string|max:255',
            'headcount' => 'required|integer|min:0',
        ]);

        $dept = new Department($request->only(['name', 'manager', 'headcount']));
        if ($request->filled('id')) {
            $dept->id = $request->input('id');
        }
        $dept->save();

        return new DepartmentResource($dept);
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'manager'   => 'sometimes|required|string|max:255',
            'headcount' => 'sometimes|required|integer|min:0',
        ]);

        $department->update($request->only(['name', 'manager', 'headcount']));

        return new DepartmentResource($department);
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();

        return response()->noContent();
    }

    // ── Roles ─────────────────────────────────────────────────────────────────

    public function indexRoles()
    {
        return RoleResource::collection(Role::orderBy('created_at')->get());
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'id'            => 'sometimes|uuid',
            'title'         => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'department_id' => 'nullable|uuid|exists:departments,id',
            'rate'          => 'required|numeric|min:0',
        ]);

        $role = new Role($request->only(['title', 'department', 'department_id', 'rate']));
        if ($request->filled('id')) {
            $role->id = $request->input('id');
        }
        $role->save();

        return new RoleResource($role);
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'title'         => 'sometimes|required|string|max:255',
            'department'    => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|nullable|uuid|exists:departments,id',
            'rate'          => 'sometimes|required|numeric|min:0',
        ]);

        $role->update($request->only(['title', 'department', 'department_id', 'rate']));

        return new RoleResource($role);
    }

    public function destroyRole(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }

    // ── Employees ─────────────────────────────────────────────────────────────

    public function indexEmployees()
    {
        return EmployeeResource::collection(Employee::orderBy('created_at')->get());
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'id'             => 'sometimes|uuid',
            'name'           => 'required|string|max:255',
            'role'           => 'required|string|max:255',
            'role_name'      => 'nullable|string|max:255',
            'capacity_role'  => 'nullable|in:frontend,backend,pm,qa,design',
            'monthly_salary' => 'required|numeric|min:0',
            'workable_hours' => 'required|integer|min:1|max:744',
            'status'         => 'required|in:Active,On Leave,Terminated',
            // cost_per_hour is GENERATED ALWAYS — never accept from client
        ]);

        $employee = new Employee($request->only([
            'name', 'role', 'role_name', 'capacity_role',
            'monthly_salary', 'workable_hours', 'status',
        ]));
        if ($request->filled('id')) {
            $employee->id = $request->input('id');
        }
        $employee->save();

        // Reload to get the DB-computed cost_per_hour
        return new EmployeeResource($employee->fresh());
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'role'           => 'sometimes|required|string|max:255',
            'role_name'      => 'sometimes|nullable|string|max:255',
            'capacity_role'  => 'sometimes|nullable|in:frontend,backend,pm,qa,design',
            'monthly_salary' => 'sometimes|required|numeric|min:0',
            'workable_hours' => 'sometimes|required|integer|min:1|max:744',
            'status'         => 'sometimes|required|in:Active,On Leave,Terminated',
            // cost_per_hour is GENERATED ALWAYS — never accept from client
        ]);

        $employee->update($request->only([
            'name', 'role', 'role_name', 'capacity_role',
            'monthly_salary', 'workable_hours', 'status',
        ]));

        return new EmployeeResource($employee->fresh());
    }

    public function destroyEmployee(Employee $employee)
    {
        $employee->delete();

        return response()->noContent();
    }

    // ── Global Overheads ──────────────────────────────────────────────────────

    public function indexOverheads()
    {
        return GlobalOverheadResource::collection(GlobalOverhead::orderBy('created_at')->get());
    }

    public function storeOverhead(Request $request)
    {
        $request->validate([
            'id'           => 'sometimes|uuid',
            'category'     => 'required|string|max:255',
            'description'  => 'required|string|max:500',
            'monthly_cost' => 'required|numeric|min:0',
        ]);

        $overhead = new GlobalOverhead($request->only(['category', 'description', 'monthly_cost']));
        if ($request->filled('id')) {
            $overhead->id = $request->input('id');
        }
        $overhead->save();

        return new GlobalOverheadResource($overhead);
    }

    public function updateOverhead(Request $request, GlobalOverhead $globalOverhead)
    {
        $request->validate([
            'category'     => 'sometimes|required|string|max:255',
            'description'  => 'sometimes|required|string|max:500',
            'monthly_cost' => 'sometimes|required|numeric|min:0',
        ]);

        $globalOverhead->update($request->only(['category', 'description', 'monthly_cost']));

        return new GlobalOverheadResource($globalOverhead);
    }

    public function destroyOverhead(GlobalOverhead $globalOverhead)
    {
        $globalOverhead->delete();

        return response()->noContent();
    }

    // ── Company Settings ──────────────────────────────────────────────────────

    public function getSettings()
    {
        $settings = CompanySetting::first();

        if (!$settings) {
            return response()->json(null, Response::HTTP_NOT_FOUND);
        }

        return new CompanySettingResource($settings);
    }

    public function upsertSettings(Request $request)
    {
        $validated = $request->validate([
            'overhead_percentage'     => 'required|numeric|min:0|max:100',
            'buffer_percentage'       => 'required|numeric|min:0|max:100',
            'yearly_fixed_cost'       => 'required|numeric|min:0',
            'employer_tax_percentage' => 'required|numeric|min:0|max:100',
            'benefits_percentage'     => 'required|numeric|min:0|max:100',
        ]);

        $tenantId = app('tenant_id');
        $settings = CompanySetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            $settings = CompanySetting::create(array_merge(['id' => $tenantId], $validated));
        }

        return new CompanySettingResource($settings);
    }
}
