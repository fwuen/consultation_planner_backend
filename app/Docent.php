<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docent extends Model
{
    protected $fillable = ['firstname', 'lastname'];
    protected $guarded = ['id', 'email'];

    function getDocentById($id)
    {
        $docent = Docent::find($id);
        return $docent;
    }

    function getDocentByMail($mail)
    {
        $docent = Docent::where('email', '=', $mail);
        return $docent;
    }

    function meeting()
    {
        return $this->hasMany('Meeting');
    }

    function meetingSeries()
    {
        return $this->hasMany('MeetingSeries');
    }
}
