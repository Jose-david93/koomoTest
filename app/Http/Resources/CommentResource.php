<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{

    public static $wrap = 'comments';
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
                'type' => 'comments',
                'id' => $this->id,
                'attributes' => [
                    'content' => $this->content,
                    'is_published' => $this->is_published,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ],
            ]
        ];
    }
}
