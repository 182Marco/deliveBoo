<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name','phone','address', 'vat_number'
    ];
    // relationship one to many with plates
    public function Plates(){
        return $this->hasMany('App\Plate');
        }
    // relationship one to many with orders
    public function orders(){
        return $this->hasMany('App\orders');
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