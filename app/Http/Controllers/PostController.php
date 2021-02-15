<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class PostController extends BaseController
{
    
    public function index(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $pages = 5;
        $posts = Post::select("id","title","slug","is_published","content","user_id",DB::raw("'posts' AS type"))
                ->with('latestComments')
                ->withCount('comments');

        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        return $this->sendResponse($posts->paginate($pages));
    }

    public function store(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);
        $post = $request->all();
        $post['user_id'] = auth('sanctum')->id();
        if(Post::find(['slug',$post->slug])->exists())
            return $this->sendError(["This record already exists"]);
        $post = Post::create($post);
        
        if(is_null($post))
            return $this->sendError(["Something went wrong while creating"]);

        return $this->sendResponse($post,201);
    }

    public function show($id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $posts = Post::with('comments')->find($id);
        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        
        return $this->sendResponse($posts->get());
    }

    public function update(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

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
                return $this->sendResponse(Post::find($id));

            return $this->sendError(["Something went wrong while updating"]);
        }
        return $this->sendError(["This comment doesn't belong to you"],401);
    }

    public function destroy($id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $post = Post::find($id);
        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_deleted = Post::find($id)->delete();
            if($is_deleted)
                return $this->sendResponse(null);
            return $this->sendError(["Something went wrong while deleting"]);
        }
        return $this->sendError(["This comment doesn't belong to you"],401);
    }
}
