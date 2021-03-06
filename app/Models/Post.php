<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'user_id'
    ];

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function latestComments() {
        //This to get an enumerable for each row.
        $subQuery = "(SELECT id, RANK() OVER (PARTITION BY post_id ORDER BY id DESC) AS rnk FROM comments) AS temp";
        return $this->hasMany(Comment::class)
        ->join(DB::raw($subQuery),'comments.id','=','temp.id')
        ->where('rnk','<=',5);
    }
}