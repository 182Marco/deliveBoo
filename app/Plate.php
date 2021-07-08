<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plate extends Model
{
    protected $fillable = [
        'name','ingredients','ingredients','visible','price','plate_img',
    ];
}
