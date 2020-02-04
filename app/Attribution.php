<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for attributions table
 *
 * @property int $image_id
 * @property string $caption
 * @property int $keywords_count
 * @mixin Eloquent
 */
class Attribution extends Model
{
    protected $table = 'attributions';

    public $timestamps = true;

    public function image()
    {
        return $this->belongsTo('App\Image');
    }

    /**
     * The keywords that belong to the attribution.
     */
    public function keywords()
    {
        return $this->belongsToMany('App\Keyword')->withTimestamps();
    }

    public function getKeywordsList()
    {
        $keywords = $this->keywords()->orderBy('order')->get();

        return $keywords->pluck('word')->toArray();
    }
}
