<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationMessage extends Model
{
    function docent_notifications()
    {
        return $this->belongsToMany('DocentNotification');
    }

    function student_notifications()
    {
        return $this->belongsToMany('StudentNotification');
    }
}
