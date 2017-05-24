<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    function participations()
    {
        return $this->hasMany('Participation');
    }

    function meeting_series()
    {
        return $this->belongsTo('MeetingSeries');
    }
}
