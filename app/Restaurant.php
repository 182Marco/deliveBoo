<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name','phone','address'
    ];
    // relationship one to many with plates
    public function Plates(){
        return $this->hasMany('App\Plate');
        }
    // relationship one to many with orders
    public function orders(){
        return $this->hasMany('App\orders');
        }
}
