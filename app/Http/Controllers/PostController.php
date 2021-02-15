<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Config;

class PostController extends BaseController
{
    
    public function index(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $pages = 5;
        $posts = Post::select("id",DB::raw("'posts' AS type"),"title","slug","is_published","content","user_id",)
                ->with('latestComments')
                ->withCount('comments');

        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        return response()->json($posts->paginate($pages));
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

        if(Post::where('slug',$post['slug'])->exists())
            return $this->sendError([Config::get('constants.messages.this_record_already_exists')]);
        $post = Post::create($post);
        
        if(is_null($post))
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_creating')]);
        
        $post['type'] = 'posts';
        return $this->sendResponse($post,Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        $posts = Post::select("id",DB::raw("'posts' AS type"),"title","slug","is_published","content","user_id",)
                ->with('latestComments')
                ->where('id',$id);

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

        if(!Post::where('id',$id)->exists())
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);

        $post = Post::find($id);

        if($this->isCurrentUserOwner($post->user_id))
        {
            if(Post::where("slug",$request->slug)->exists())
                return $this->sendError([Config::get('constants.messages.this_record_already_exists')]);
            
            $is_updated = Post::find($id)->update($request->all());
            if($is_updated)
            {
                $post = Post::find($id);
                $post['type'] = "posts";
                return $this->sendResponse($post);
            }
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_updating')]);
        }
        return $this->sendError([Config::get('constants.messages.this_post_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }

    public function destroy(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);

        if(!Post::where('id',$id)->exists())
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);

        $post = Post::find($id);
        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_deleted = Post::find($id)->delete();
            if($is_deleted)
                return $this->sendResponse(null);
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_deleting')]);
        }
        return $this->sendError([Config::get('constants.messages.this_post_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }
}
