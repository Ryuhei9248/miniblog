<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\ReplyRequest;
use App\Models\Reply;
use App\Models\User;

class PostController extends Controller
{
    public function index()
    {
        $posts=Post::with(['user','bookmarkingUsers'])->orderBy('created_at', 'desc')->get();

        return view('index',['posts' => $posts]);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(CreateRequest $request)
    {
        $post = new Post;
        $post->fill($request->all());
        $post->user()->associate(Auth::user()); // ★
        $post->save();

        return redirect()->to('/'); // '/' へリダイレクト
    }

    public function delete(Post $post)
    {
        // 投稿者本人でなければ削除を許可しない
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->to('/');
    }

    public function show(Post $post)
    {
        $post->load('replies.user');
        $bookmarked = $post->bookmarkingUsers->contains(Auth::id()); 

        return view('posts.show', ['post' => $post, 'bookmarked' => $bookmarked]);

    }

    public function reply(ReplyRequest $request, Post $post)
    {
        $reply = new Reply;
        $reply->fill($request->all());
        $reply->user()->associate(Auth::user());
        $reply->post()->associate($post);
        $reply->save();

        return redirect()->back();
    }
}
