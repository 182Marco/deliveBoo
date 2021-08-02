<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name','phone','city','address','img'
    ];
    // relationship one to many with plates
    public function plates(){
        return $this->hasMany('App\Plate');
        }
    // relationship one to many with orders
    public function orders(){
        return $this->hasMany('App\Order');
        }
    // -------
    //define relationship with user one to one
    public function user() {
        return $this->belongsTo('App\User');
    }
    // -------
    //define relationship with types many to many
    public function types() {
        return $this->belongsToMany('App\Type');
    }
}
