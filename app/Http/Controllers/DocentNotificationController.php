<?php

namespace App\Http\Controllers;

use App\DocentNotification;

class DocentNotificationController extends Controller
{
        public function __construct()
    {
        $this->middleware('auth.token');
    }
    public function index($id)
    {
        $docent_notifications = DocentNotification::where('docent_id', '=', $id)->get();
        return response()->json($docent_notifications);
    }
}
