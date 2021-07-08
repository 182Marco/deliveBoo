<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plate extends Model
{
    //RELATION with USERS (users-plates) one to many
    public function users() {
        return $this->hasMany('App\User');
    }
}
