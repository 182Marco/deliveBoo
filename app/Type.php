<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name'
    ];
    // -------
    //define relationship with restaurants many to many
    public function restaurants() {
        return $this->belongsToMany('App\Restaurant');
    }
}
