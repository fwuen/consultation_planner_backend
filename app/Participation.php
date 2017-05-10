<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
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
