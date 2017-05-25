<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    function notification_messages()
    {
        return $this->hasOne('NotificationMessage');
    }

    function student()
    {
        return $this->belongsTo('Student');
    }
}
