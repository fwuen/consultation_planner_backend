<?php

namespace App\Http\Controllers;

use App\StudentNotification;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    public function index($id)
    {
        $student_notifications = StudentNotification::where('student_id', '=', $id)->get();
        return response()->json($student_notifications);
    }
}
