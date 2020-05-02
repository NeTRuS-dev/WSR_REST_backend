<?php

namespace App\Http\Resources;

use App\Photo;
use Illuminate\Http\Resources\Json\JsonResource;

class Photos extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Photo $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => asset("storage/{$this->path}"),
            'owner_id' => $this->owner_id,
            'users' => $this->getAllAlowedUsers()->all()
        ];
    }
}
