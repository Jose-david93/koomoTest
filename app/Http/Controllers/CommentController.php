<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Config;

class CommentController extends BaseController
{
    public function store(Request $request)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $request->validate([
            'content' => 'required',
            'post_id' => 'required',
            'is_published' => 'required'
        ]);
        $comment = $request->all();
        $comment['user_id'] = auth('sanctum')->id();
        $comment = Comment::create($comment);
        
        if(is_null($comment))
        {
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_creating')]);
        }
        
        $comment['type'] = 'comments';
        return $this->sendResponse($comment,201);
    }

    public function showByPostId(Request $request, $id)
    {
        $pages = 5;
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $comments = Comment::where('post_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));
        if(!auth('sanctum')->check())
        {
            $comments = $comments->where("is_published",true);
        }

        return response()->json($comments->paginate($pages));
    }

    public function showByUserId(Request $request, $id)
    {
        $pages = 5;
        $requestHeaders = $this->validateHeaders($request);

        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $comments = Comment::where('user_id',$id)
        ->select('id','content','is_published','user_id','post_id', DB::raw("'comments' AS type"));
        if(!auth('sanctum')->check())
        {
            $comments = $comments->where("is_published",true);
        }
        
        $comments = $comments->get();
        return response()->json($comments->paginate($pages));
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
            'content' => 'required',
            'is_published' => 'required'
        ]);

        if(!Comment::where('id',$id)->exists())
        {
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);
        }
            
        $comment = Comment::find($id);

        if($this->isCurrentUserOwner($comment->user_id))
        {
            $is_updated = Comment::find($id)->update($request->all());
            if($is_updated)
            {
                $comment = Comment::find($id);
                $comment['type'] = 'comments';
                return $this->sendResponse($comment);
            }
            
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_updating')]);
        }
        return $this->sendError([Config::get('constants.messages.this_comment_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }

    public function destroy(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        if(!Comment::where('id',$id)->exists())
        {
            return $this->sendError([Config::get('constants.messages.the_id_that_you_are_looking_for_does_not_exist')]);
        }
        
        $comment = Comment::find($id);
        if($this->isCurrentUserOwner($comment->user_id))
        {
            $is_deleted = Comment::find($id)->delete();
            if($is_deleted)
            {
                return $this->sendResponse(null);
            }
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_deleting')]);
        }
        return $this->sendError([Config::get('constants.messages.this_comment_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }
}
