<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocentNotification extends Model
{
    protected $fillable = ['seen'];
    function notification_messages()
    {
        return $this->hasOne('NotificationMessage');
    }

    function docent()
    {
        return $this->belongsTo('Docent');
    }

    function meeting()
    {
        return $this->belongsTo('Meeting');
    }

}
