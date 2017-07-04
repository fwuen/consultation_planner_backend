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
        'description',
        'room',
        'last_enrollment',
        'cancelled',
        'participants_count',
        'has_passed'];
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

    function slots()
    {
        return $this->hasMany('Slot');
    }

    public function checkDates() {
        $end = new \DateTime(''.$this->end, new \DateTimeZone("Europe/Berlin"));
        $now = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        $end->format('Y-m-d H:i:s');
        $now->format('Y-m-d H:i:s');
        if($end < $now) {
            $this->has_passed = 1;
            $this->save();
        }
    }
}
