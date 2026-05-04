<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Http\Resources\ContractResource;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::all();
        return ContractResource::collection($contracts);
    }

    public function show(Contract $contract)
    {
        return new ContractResource($contract);
    }

    public function update(Request $request, Contract $contract)
    {
        $contract->update($request->only(['status', 'notes', 'end_date', 'total_value']));
        return new ContractResource($contract->fresh());
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->noContent();
    }
}
