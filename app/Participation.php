<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    protected $fillable = ['email_notification_student'];
    protected $guarded = ['student_id', 'meeting_id'];

    function student()
    {
        $this->belongsTo('Student');
    }

    function meeting()
    {
        $this->belongsTo('Meeting');
    }

    function getParticipationById($id)
    {
        $participation = Participation::find($id);
        return $participation;
    }
}
