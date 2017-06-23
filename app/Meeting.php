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

    //TODO checken, ob die überprüfung so passt
    public function checkDates() {
        $end = new \DateTime(''.$this->end, new \DateTimeZone("Europe/Berlin"));
        $now = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        if($end < $now) {
            $this->has_passed = 1;
            $this->save();
        }
    }
}
