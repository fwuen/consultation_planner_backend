<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $fillable = ['firstname', 'lastname'];
    protected $guarded = ['id', 'email'];

    function participation()
    {
        return $this->hasMany('Participation');
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
