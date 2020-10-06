<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;

/**
 * @property int $image_id
 * @property $date;
 * @property $price;
 * @property int $sales_type_id;
 * @mixin Eloquent
 */
class Sale extends Model
{
    protected $table = 'sales';

    public $timestamps = true;

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
