<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhoto;
use App\Photo;
use App\Comment;
use App\Http\Requests\StoreComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhotoController extends Controller
{
    public function __construct()
    {
        // 認証が必要
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * 写真投稿
     * @param StorePhoto $request
     * @return \Illuminate\Http\Response
     */
    public function create(StorePhoto $request)
    {

        $file_name = request()->photo->getClientOriginalName();
	    
        $photo = new Photo();
        $photo->file_path = '/storage/'. $file_name;
        // データベースエラー時にファイル削除を行うため
        // トランザクションを利用する
        DB::beginTransaction();

        try {
            Auth::user()->photos()->save($photo);
            DB::commit();
            request()->photo->storeAs('public/',$file_name);
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        // リソースの新規作成なので
        // レスポンスコードは201(CREATED)を返却する
        return response($photo, 201);
    }

    /**
     * 写真一覧
     */
    public function index()
    {
        $photos = Photo::with(['owner', 'likes'])
            ->orderBy(Photo::CREATED_AT, 'desc')->paginate();
    
        return $photos;
    }

    /**
     * 写真詳細
     * @param string $id
     * @return Photo
     */
    public function show(string $id)
    {
        $photo = Photo::where('id', $id)
            ->with(['owner', 'comments.author', 'likes'])->first();

        return $photo ?? abort(404);
    }

    /**
     * コメント投稿
     * @param Photo $photo
     * @param StoreComment $request
     * @return \Illuminate\Http\Response
     */
    public function addComment(Photo $photo, StoreComment $request)
    {
        $comment = new Comment();
        $comment->content = $request->get('content');
        $comment->user_id = Auth::user()->id;
        $photo->comments()->save($comment);

        // authorリレーションをロードするためにコメントを取得しなおす
        $new_comment = Comment::where('id', $comment->id)->with('author')->first();

        return response($new_comment, 201);
    }

    /**
     * いいね
     * @param string $id
     * @return array
     */
    public function like(int $id)
    {
        $photo = Photo::where('id', $id)->with('likes')->first();

        if (! $photo) {
            abort(404);
        }

        $photo->likes()->detach(Auth::user()->id);
        $photo->likes()->attach(Auth::user()->id);

        return ["photo_id" => $id];
    }

    /**
     * いいね解除
     * @param string $id
     * @return array
     */
    public function unlike(int $id)
    {
        $photo = Photo::where('id', $id)->with('likes')->first();

        if (! $photo) {
            abort(404);
        }

        $photo->likes()->detach(Auth::user()->id);

        return ["photo_id" => $id];
    }
}
