<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\PaginatePostResource;
use App\Http\Resources\PostResource;

use Config;

class PostController extends BaseController
{
    
    public function index(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $posts = Post::with('latestComments')->withCount('comments');

        if(!auth('sanctum')->check())
        {
            $posts = $posts->where('is_published',true);
        }
        return response()->json(PaginatePostResource::collection($posts->paginate(Config::get('constants.configurations.rows_per_page')))->response()->getData(true));
    }

    public function store(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);

        if(Post::where('slug',$request->slug)->exists())
        {
            return $this->sendError([Config::get('constants.messages.this_record_already_exists')]);
        }
        $post = $request->all();
        $post['user_id'] = auth('sanctum')->id();
        $post = Post::create($post);
        $post = Post::where('id',$post->id)->get();
        return $this->sendResponse(PostResource::collection($post),Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $posts = Post::with('latestComments')->where('id',$id);

        if(!auth('sanctum')->check())
        {
            $posts = $posts->where('is_published',true);
        }
        $posts = $posts->get();
        if($posts->isEmpty())
            return $this->sendNullResponse();

        return $this->sendResponse(PostResource::collection($posts));
    }

    public function update(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
            'is_published' => 'required'
        ]);

        if(!Post::where('id',$id)->exists())
        {
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);
        }

        $post = Post::where('id',$id);

        if($this->isCurrentUserOwner($post->user_id))
        {
            if(Post::where('slug',$request->slug)->exists())
            {
                return $this->sendError([Config::get('constants.messages.this_record_already_exists')]);
            }
            
            $is_updated = Post::find($id)->update($request->all());
            if($is_updated)
            {
                $post = Post::where('id',$id)->get();
                return $this->sendResponse(PostResource::collection($post));
            }
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_updating')]);
        }
        return $this->sendError([Config::get('constants.messages.this_post_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }

    public function destroy(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        if(!Post::where('id',$id)->exists())
        {
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);
        }

        $post = Post::find($id);
        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_deleted = Post::where('id',$id)->delete();
            if($is_deleted)
            {
                return $this->sendNullResponse();
            }
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_deleting')]);
        }
        return $this->sendError([Config::get('constants.messages.this_post_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }
}
