<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\Participation;
use Illuminate\Http\Request;

//TODO sämtliche updates (und deletes) gehen noch nicht
class StudentParticipationController extends Controller
{
    public function show($id, Participation $participation)
    {
        return response()->json($participation);
    }

    public function index($id)
    {
        $meetings = Participation::where('student_id', '=', $id)->join('meetings', 'participations.meeting_id', '=', 'meetings.id')->select('meetings.*')->get();
        return response()->json($meetings);
    }

    public function store($id, Request $request)
    {
        //TODO datetime validate
        $this->validate($request,[
            'student_id' => 'required|max:10|unsigned',
            'meeting_id' => 'required|max:10|unsigned',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'email_notification_student' => 'required'
        ]);

        $participation = new Participation();
        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');

        return redirect('student/' . $id . '/participation');
    }

    public function update($id, Request $request, Participation $participation)
    {
        $this->validate($request,[
            'student_id' => 'required|max:10|unsigned',
            'meeting_id' => 'required|max:10|unsigned',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'email_notification_student' => 'required'
        ]);

        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');

        return redirect('student/' . $id . '/participation');
    }

    public function destroy($id, Participation $participation)
    {
        $participation->delete();
        return redirect('student/' . $id . '/participation');
    }
}
