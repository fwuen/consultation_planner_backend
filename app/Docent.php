<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docent extends Model
{
    function meeting()
    {
        return $this->hasMany('Meeting');
    }

    function meetingSeries()
    {
        return $this->hasMany('MeetingSeries');
    }
}
