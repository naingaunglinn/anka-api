<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create($request->all());
        return new InvoiceResource($invoice);
    }

    public function pay(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'Paid', 'paid_at' => now()]);
            DB::table('contracts')
                ->where('id', $invoice->contract_id)
                ->increment('revenue_recognized', $invoice->total ?? 0);
        });

        return new InvoiceResource($invoice->fresh());
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
