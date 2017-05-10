<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    function participation()
    {
        $this->hasMany('Participation');
    }

    function getStudentById($id)
    {
        $student = Student::find($id);
        return $student;
    }

    function getStudentByMail($mail)
    {
        $student = Student::where('email', '=', $mail);
        return $student;
    }
}
