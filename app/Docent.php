<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docent extends Model
{
    protected $fillable = ['firstname', 'lastname', 'academic_title', 'email'];
    function meeting_series()
    {
        return $this->hasMany('MeetingSeries');
    }

    function docent_notifications()
    {
        return $this->hasMany('DocentNotification');
    }
}
