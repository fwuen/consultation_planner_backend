<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\Participation;
use App\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function show(Student $student)
    {
        return response()->json($student);
    }

    public function store(Request $request)
    {
        $this->doBasicStudentValidation($request);
        $student = new Student;
        $this->setAndSaveStudentProperties($student, $request);
        return redirect('student/' . $student->id);
    }

    public function update(Request $request, Student $student)
    {
        $this->doBasicStudentValidation($request);
        $this->setAndSaveStudentProperties($student, $request);
        return redirect('student/' . $student->id);
    }

    private function doBasicStudentValidation(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|max:255|unique:students',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255'
        ]);
    }

    private function setAndSaveStudentProperties(Student $student, Request $request)
    {
        $student->firstname = $request->get('firstname');
        $student->lastname = $request->get('lastname');
        $student->email = $request->get('email');
        $student->save();
    }
}
