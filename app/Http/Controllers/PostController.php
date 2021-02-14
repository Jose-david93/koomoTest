<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('latestComments')->withCount('comments');
        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        return response()->json($posts->paginate(5));
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
            return response(['message' => 'This record already exists'], 400);
        $post = Post::create($post);
        return response()->json($post, 201);
    }

    public function show($id)
    {
        $posts = Post::with('comments')->find($id);
        if(!auth('sanctum')->check())
            $posts = $posts->where("is_published",true);
        return response()->json($posts);
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

        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_updated = Post::find($id)->update($request->all());
            if($is_updated)
                return response(['message' => 'Update successfully'], 200);
            return response(['message' => 'Something went wrong while updating'], 400);
        }
        return response(['message' => "This post doesn't belong to you"], 401);

    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if(auth('sanctum')->id() === $post->user_id)
        {
            $is_deleted = Post::find($id)->delete();
            if($is_deleted)
                return response(['message' => 'Deleted successfully'], 200);
            return response(['message' => 'Something went wrong while deleting'], 400);
        }
        return response(['message' => "This post doesn't belong to you"], 401);
    }
}
