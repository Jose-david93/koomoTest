<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;

class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::with('latestComments')->withCount('comments');
        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        return $this->sendResponse($posts->paginate(5));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);
        $post = $request->all();
        $post['user_id'] = auth('sanctum')->id();
        if(Post::find(['slug',$post->slug])->exists())
            return $this->sendError("This record already exists");
        $post = Post::create($post);

        if(is_null($post))
            return $this->sendError("Something went wrong while creating");

        return $this->sendResponse($post,201);
    }

    public function show($id)
    {
        $posts = Post::with('comments')->find($id);
        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        
        return $this->sendResponse($posts->get());
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);
        $post = Post::find($id);

        if($this->isCurrentUserOwner($post->user_id))
        {
            $is_updated = Post::find($id)->update($request->all());
            if($is_updated)
                return $this->sendResponse("Update successfully");

            return $this->sendError("Something went wrong while updating");
        }
        return $this->sendError("This comment doesn't belong to you",401);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_deleted = Post::find($id)->delete();
            if($is_deleted)
                return $this->sendResponse("Deleted successfully");
            return $this->sendError("Something went wrong while deleting");
        }
        return $this->sendError("This comment doesn't belong to you",401);
    }
}
