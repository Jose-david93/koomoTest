<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'is_published',
        'post_id',
        'user_id'
    ];

    public function getCommentsByPostId($id)
    {
        $response = $this->where('post_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));

        if(!auth('sanctum')->check())
        {
            $response = $response->where('is_published',true);
        }
        return $response;
    }

    public function getCommentsByUserId($id)
    {
        $response = $this->where('user_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));

        if(!auth('sanctum')->check())
        {
            $response = $response->where('is_published',true);
        }
        
        return $response;
    }

}
