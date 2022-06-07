<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Photo extends Model
{
    protected $fillable = [
    'file_path',
  ];

    /** JSONに含めない属性 */
    protected $hidden = [
      'user_id',
      self::CREATED_AT, self::UPDATED_AT,
  ];

  /**
   * リレーションシップ - usersテーブル
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function owner()
  {
      return $this->belongsTo('App\User', 'user_id', 'id', 'users');
  }

}
