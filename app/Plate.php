<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plate extends Model
{
    protected $fillable = [
        'name','ingredients','description','visible','price','img','restaurant_id'
    ];
    // relationship one to many with restaurants
    public function restaurant(){
       return $this->belongsTo('App\Restaurant');
    }
    // -------
    //define relationship with orders many to many
    public function orders() {
        return $this->belongsToMany('App\Order');
    }
}
