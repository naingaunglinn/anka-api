<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'deal_id'             => $this->deal_id,
            'contract_number'     => $this->contract_number,
            'client'              => $this->client,
            'total_value'         => $this->total_value,
            'revenue_recognized'  => $this->revenue_recognized,
            'status'              => $this->status,
            'start_date'          => $this->start_date?->toDateString(),
            'end_date'            => $this->end_date?->toDateString(),
            'notes'               => $this->notes,
        ];
    }
}
