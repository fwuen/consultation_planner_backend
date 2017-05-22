<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingSeries extends Model
{
    // TODO
    protected $fillable = ['first_meeting', 'last_meeting'];
    protected $guarded = ['id', 'docent_id'];

    function docent()
    {
        return $this->belongsTo('Docent');
    }
}
