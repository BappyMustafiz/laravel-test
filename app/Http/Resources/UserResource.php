<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'user_name' => $this->user_name,
            'email' => $this->email,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : '',
            'registered_at' => $this->registered_at ? $this->registered_at->format('d-m-Y h:i:s A') : '',
            'token' => $this->token ? $this->token : '',
        ];
    }
}
