<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    protected $fillable = ['start', 'end', 'remark', 'email_notification_student'];
    function student()
    {
        return $this->belongsTo('Student');
    }

    function meeting()
    {
        return $this->belongsTo('Meeting');
    }

    function slots()
    {
        return $this->hasMany('Slot');
    }
}
