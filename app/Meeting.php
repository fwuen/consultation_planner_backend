<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'start',
        'end',
        'slots',
        'max_participants',
        'email_notification_docent',
        'title',
        'description_public',
        'description_private',
        'room',
        'last_enrollment',
        'cancelled'];
    function participations()
    {
        return $this->hasMany('Participation');
    }

    function meeting_series()
    {
        return $this->belongsTo('MeetingSeries');
    }

    function docent_notification()
    {
        return $this->hasMany('DocentNotification');
    }
}
