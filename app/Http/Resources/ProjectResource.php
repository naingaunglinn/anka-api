<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'contract_id'    => $this->contract_id,
            'project_number' => $this->project_number,
            'name'           => $this->name,
            'client'         => $this->client,
            'budget_hours'   => $this->budget_hours,
            'consumed_hours' => $this->consumed_hours,
            'status'         => $this->status,
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
        ];
    }
}
