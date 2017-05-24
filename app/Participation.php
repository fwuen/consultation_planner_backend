<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    function student()
    {
        return $this->belongsTo('Student');
    }

    function meeting()
    {
        return $this->belongsTo('Meeting');
    }
}
