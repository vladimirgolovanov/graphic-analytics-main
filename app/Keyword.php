<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for keywords table
 *
 * @mixin Eloquent
 */
class Keyword extends Model
{
    protected $table = 'keywords';

    public $timestamps = true;

    protected $fillable = ['word'];

    /**
     * The attributions that belong to the keyword.
     */
    public function attributions()
    {
        return $this->belongsToMany('App\Attribution')->withTimestamps();
    }
}
