<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginatePostResource extends JsonResource
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
                'type' => 'posts',
                'id' => $this->id,
                'attributes' => [
                    'content' => $this->content,
                    'title' => $this->title,
                    'comments_count' => $this->comments_count,
                    'is_published' => $this->is_published,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at
                ],
                'relationships' => [
                    'links' => [
                        'commentByPost' => 'http://127.0.0.1:8000/api/commentsByPost/'.$this->id,
                    ],
                    'comments' => [
                        'data' =>  PaginateCommentResource::collection($this->latestComments)
                    ]
                ],
                'links' => [
                    'self' => 'http://127.0.0.1:8000/api/posts/'.$this->id
                ]
            ];
    }
}
