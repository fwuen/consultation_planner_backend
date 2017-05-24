<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docent extends Model
{
    function meeting_series()
    {
        return $this->hasMany('MeetingSeries');
    }
}
