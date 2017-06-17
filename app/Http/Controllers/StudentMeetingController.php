<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\Participation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StudentMeetingController extends Controller
{
    public function index($id)
    {
        $participations = Participation::where('student_id', '=', $id)->get();

        $meetings = new Collection();

        foreach($participations as $participation) {
            $meeting = Meeting::findOrFail($participation->meeting_id);
            $meeting->checkDates();
            $meetings->add($meeting);
        }

        return response()->json($meetings);
    }

    public function show($id, Meeting $meeting)
    {
        $meeting->checkDates();
        return response()->json($meeting);
    }
}
