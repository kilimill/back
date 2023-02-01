<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private function getResource(): User
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $user = $this->getResource();

        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->getMedia('avatars')->pluck('original_url')->first() ?? null,
            'phone' => $user->phone,
            'role_id' => $user->role_id,
            'first_name_owner' => $user->first_name_owner,
            'last_name_owner' => $user->last_name_owner,
            'phone_owner' => $user->phone_owner,
            'email_owner' => $user->email_owner,
            'inn_owner' => $user->inn_owner,
            'kpp_owner' => $user->kpp_owner,
            'legal_form_id' => $user->legal_form_id,
        ];
    }
}
