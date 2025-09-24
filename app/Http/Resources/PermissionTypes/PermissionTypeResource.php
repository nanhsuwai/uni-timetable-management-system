<?php

namespace App\Http\Resources\PermissionTypes;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'permissions' => $this->permissions
        ];
    }
}
