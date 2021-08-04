<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'rate','restaurant_id'
    ];
    // relationship one to many with restaurants
    public function rest(){
        return $this->belongsTo('App\Restaurant');
    }
}
