<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'employee_id' => $this->employee_id,
            'approved_by' => $this->approved_by,
            'task'        => $this->task,
            'date'        => $this->date?->toDateString(),
            'hours'       => $this->hours,
            'billable'    => $this->billable,
            'status'      => $this->status,
            'notes'       => $this->notes,
            'approved_at' => $this->approved_at,
        ];
    }
}
