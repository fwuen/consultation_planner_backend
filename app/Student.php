<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['firstname', 'lastname',  'email'];
    function participations()
    {
        return $this->hasMany('Participation');
    }

    function student_notifications()
    {
        return $this->hasMany('StudentNotification');
    }
}
