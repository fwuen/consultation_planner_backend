<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingSeries extends Model
{
    protected $guarded = ['id', 'docent_id'];

    function docent()
    {
        return $this->belongsTo('Docent');
    }
}
