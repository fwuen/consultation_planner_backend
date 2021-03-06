<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingSeries extends Model
{
    function docent()
    {
        return $this->belongsTo('Docent');
    }

    function meetings()
    {
        return $this->hasMany('Meeting');
    }
}
