<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'links' => [
                'self' => 'http://127.0.0.1:8000/api/posts/'.$this->id
            ],
            'data' => [
                'type' => 'posts',
                'id' => $this->id,
                'attributes' => [
                    'user_id' => $this->user_id,
                    'content' => $this->content,
                    'title' => $this->title,
                    'is_published' => $this->is_published,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at
                ],
            ]
        ];
    }
}
