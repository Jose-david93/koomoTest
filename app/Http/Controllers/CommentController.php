<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'post_id' => 'required',
            'is_published' => 'required'
        ]);
        $comment = $request->all();
        $comment['user_id'] = auth('sanctum')->id();
        $comment = Comment::create($comment);
        return response()->json($comment, 201);
    }

    public function showByPostId($id)
    {
        $comments = Comment::where('post_id',$id);
        if(!auth('sanctum')->check())
            $comments = $comments->where("is_published",true);
        return response()->json($comments->get());
    }

    public function showByUserId($id)
    {
        $comments = Comment::where('user_id',$id);
        if(!auth('sanctum')->check())
            $comments = $comments->where("is_published",true);
        return response()->json($comments->get());
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);
        $comment = Post::find($id);

        if(auth('sanctum')->id() === $comment->user_id)
        {
            $is_updated = Comment::find($id)->update($request->all());
            if($is_updated)
                return response(['message' => 'Update successfully'], 200);
            return response(['message' => 'Something went wrong while updating'], 400);
        }
        return response(['message' => "This comment doesn't belong to you"], 401);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if(auth('sanctum')->id() === $comment->user_id)
        {
            $is_deleted = Comment::find($id)->delete();
            if($is_deleted)
                return response(['message' => 'Deleted successfully'], 200);
            return response(['message' => 'Something went wrong while deleting'], 400);
        }
        return response(['message' => "This comment doesn't belong to you"], 401);
    }
}
