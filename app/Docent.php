<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docent extends Model
{
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
        $this->hasMany('Meeting');
    }
}
