<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth; 

class Photo extends Model
{
    protected $fillable = [
    'file_path',
    ];

   /** JSONに含めるアクセサ */
    protected $appends = [
      'likes_count', 'liked_by_user',
    ];

    /** JSONに含める属性 */
    protected $visible = [
    'id', 'owner', 'file_path', 'comments',
    'likes_count', 'liked_by_user',
   ];

    /** JSONに含めない属性 */
    protected $hidden = [
      'user_id',
      self::CREATED_AT, self::UPDATED_AT,
   ];

    protected $perPage = 6;

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id', 'id', 'users');
    }

    /**
     * リレーションシップ - commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Comment')->orderBy('id', 'desc');
    }

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany('App\User', 'likes')->withTimestamps();
    }

    /**
     * アクセサ - likes_count
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes->count();
    }

    /**
     * アクセサ - liked_by_user
     * @return boolean
     */
    public function getLikedByUserAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->likes->contains(function ($user) {
            return $user->id === Auth::user()->id;
        });
    }
}
