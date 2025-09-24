<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'user_type' => $this->user_type,
            'permissions' => $this->permissions
        ];
    }
}
