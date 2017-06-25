<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    function participations()
    {
        return $this->belongsTo('Participation');
    }

    function meetings()
    {
        return $this->belongsTo('Meeting');
    }
}
