<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\PaginateCommentResource;
use App\Http\Resources\CommentResource;
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
        $comment = Comment::where('id',$comment->id)->get();
        return $this->sendResponse(CommentResource::collection($comment),Response::HTTP_CREATED);
    }

    public function showByPostId(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $comment = new Comment();
        $comments = $comment->getCommentsByPostId($id);

        return response()->json(PaginateCommentResource::collection($comments->paginate(Config::get('constants.configurations.rows_per_page')))->response()->getData(true));
    }

    public function showByUserId(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }
        $comment = new Comment();
        $comments = $comment->getCommentsByUserId($id);
        return response()->json(PaginateCommentResource::collection($comments->paginate(Config::get('constants.configurations.rows_per_page')))->response()->getData(true));
    }

    public function update(Request $request, $id)
    {
        $requestHeaders = $this->validateHeaders($request);
        if(!$requestHeaders['isValid'])
        {
            return $this->sendError($requestHeaders['message'],$requestHeaders['code']);
        }

        $request->validate([
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
            $is_updated = Comment::where('id',$id)->update($request->all());
            if($is_updated)
            {
                $comment = Comment::where('id',$id)->get();
                return $this->sendResponse(CommentResource::collection($comment));
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
            $is_deleted = Comment::where('id',$id)->delete();
            if($is_deleted)
            {
                return $this->sendNullResponse();
            }
            return $this->sendError([Config::get('constants.messages.something_went_wrong_while_deleting')]);
        }
        return $this->sendError([Config::get('constants.messages.this_comment_doesnt_belong_to_you')],Response::HTTP_UNAUTHORIZED);
    }
}
