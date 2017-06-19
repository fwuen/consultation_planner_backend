<?php

namespace App\Http\Controllers;

use App\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255'
        ]);

        $student = new Student;
        $student->firstname = $request->get('firstname');
        $student->lastname = $request->get('lastname');
        $student->email = $request->get('email');
        $student->save();

        return redirect()->route('/student/' . $student->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Student $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Student $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $this->validate($request, [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255'
        ]);

        /*
        $student->firstname = $request->get('firstname');
        $student->lastname = $request->get('lastname');
        $student->email = $request->get('email');
        */

        //TODO prüfen, ob das geht, ansonsten entfernen und auskommentierten Teil verwenden
        $informationForStudentUpdate = $request->all();
        $student->fill($informationForStudentUpdate)->save();
        return redirect()->route('/student/' . $student->id);
    }

}
