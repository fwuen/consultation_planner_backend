<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    function participation()
    {
        return $this->hasMany('Participation');
    }
}
