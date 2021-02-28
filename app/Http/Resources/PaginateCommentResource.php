<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginateCommentResource extends JsonResource
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
            'type' => 'comments',
            'id' => $this->id,
            'attributes' => [
                'content' => $this->content,
                'is_published' => $this->content,
            ],
            'relationships' => [
                'comments_by_user' =>[
                    'links' => [
                        'self' => 'http://127.0.0.1:8000/api/commentsByUser/'.$this->user_id
                    ],
                    'data' => [
                        'type' => 'comments',
                        'id' => $this->user_id
                    ]
                ],
                'comments_by_post' =>[
                    'links' => [
                        'self' => 'http://127.0.0.1:8000/api/commentsByPost/'.$this->post_id
                    ],
                    'data' => [
                        'type' => 'comments',
                        'id' => $this->user_id
                    ]
                ]
            ],
            'links' => [
                'selfByUser' => 'http://127.0.0.1:8000/api/commentsByUser/'.$this->user_id,
                'selfByPost' => 'http://127.0.0.1:8000/api/commentsByPost/'.$this->post_id
            ]
        ];
    }
}
