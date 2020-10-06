<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;

/**
 * @property int $image_id
 * @property string $name;
 * @mixin Eloquent
 */
class SalesType extends Model
{
    protected $table = 'sales_types';
}
