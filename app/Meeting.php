<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    function participation()
    {
        $this->hasMany('Particupation');
    }

    function docent()
    {
        $this->belongsTo('Docent');
    }

    function getMeetingById($id)
    {
        $meeting = Meeting::find($id);
        return $meeting;
    }
}
