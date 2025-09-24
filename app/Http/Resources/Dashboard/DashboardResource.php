<?php

namespace App\Http\Resources\Dashboard;

use App\Models\Batch;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request) : array
    {
        $batch_code = Batch::where('batch_key', $this->selected_batch_key)->first()?->training_code_name;

        return [
            'id' => $this->id,
            'name' => $this->candidate->myanmar_name ?? '-',
            'phone' => $this->candidate->phone ?? '-',
            'position' => $this->candidate->position->name ?? '-',
            'ministry' => $this->candidate->ministry->name ?? '-',
            'batch_code' => $batch_code ?? '-',
        ];
    }
}
