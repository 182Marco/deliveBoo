<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
         'price','customer_name','customer_lastName','customer_phone','customer_address','restaurant_id','customer_email'
    ];
    // define relationship with restaurant
    public function restaurant(){
        return $this->belongsTo('App\Restaurant');
    }
    // -------
    //define relationship with plates many to many
    public function plates() {
        return $this->belongsToMany('App\Plate');
    }
}
