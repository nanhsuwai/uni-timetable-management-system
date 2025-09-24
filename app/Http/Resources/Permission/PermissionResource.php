<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    
    public function toArray($request) : array
    {
        // return parent::toArray($request);
       
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'module_name' => $this->module_name
        ];
    }
}
