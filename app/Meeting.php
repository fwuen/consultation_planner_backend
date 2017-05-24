<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    function participation()
    {
        return $this->hasMany('Participation');
    }

    function docent()
    {
        return $this->belongsTo('Docent');
    }
}
