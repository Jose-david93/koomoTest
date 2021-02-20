<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->where('post_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));
    }

    public function getCommentsByUserId($id)
    {
        return $this->where('user_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));
    }

}
