<?php

namespace App\Http\Controllers;

use App\StudentNotification;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    //TODO joinen, damit man an die student_id kommt oder eventuell student_id in participation speichern
    public function index($id)
    {
        $student_notifications = StudentNotification::where('student_id', '=', $id)->get();
        return response()->json($student_notifications);
    }
}
