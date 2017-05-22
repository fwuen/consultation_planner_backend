<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = ['start', 'end', 'slots', 'email_notification_docent', 'description_public', 'description_private', 'title', 'room', 'last_enrollment'];
    protected $guarded = ['id', 'docent_id'];

    function participation()
    {
        return $this->hasMany('Particupation');
    }

    function docent()
    {
        return $this->belongsTo('Docent');
    }

    function getMeetingById($id)
    {
        $meeting = Meeting::find($id);
        return $meeting;
    }
}
