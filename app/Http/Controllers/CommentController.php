<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class CommentController extends BaseController
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
        
        if(is_null($comment))
            return $this->sendError("Something went wrong while creating");

        return $this->sendResponse($comment,201);
    }

    public function showByPostId($id)
    {
        $comments = Comment::where('post_id',$id);
        if(!auth('sanctum')->check())
            $comments = $comments->where("is_published",true);

        return $this->sendResponse($comments->get());
    }

    public function showByUserId($id)
    {
        $comments = Comment::where('user_id',$id);
        if(!auth('sanctum')->check())
            $comments = $comments->where("is_published",true);
        
        return $this->sendResponse($comments->get());
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

        if($this->isCurrentUserOwner($comment->user_id))
        {
            $is_updated = Comment::find($id)->update($request->all());
            if($is_updated)
                return $this->sendResponse("Update successfully");
            
            return $this->sendError("Something went wrong while updating");
        }
        return $this->sendError("This comment doesn't belong to you",401);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if($this->isCurrentUserOwner($comment->user_id))
        {
            $is_deleted = Comment::find($id)->delete();
            if($is_deleted)
                return $this->sendResponse("Deleted successfully");

            return $this->sendError("Something went wrong while deleting");
        }
        return $this->sendError("This comment doesn't belong to you",401);
    }
}
