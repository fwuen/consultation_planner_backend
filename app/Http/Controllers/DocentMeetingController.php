<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\Collection;
use App\Meeting;
use App\MeetingSeries;
use Illuminate\Http\Request;

class DocentMeetingController extends Controller
{
    public function index($id) {
        $meeting_series = MeetingSeries::where('docent_id', '=', $id)->get();

        $meetings = new Collection();

        foreach($meeting_series as $series) {
            $meetings_in_series = Meeting::where('meeting_series_id', '=', $series->id)->get();
            $meetings->add($meetings_in_series);
        }
        return response()->json($meetings);
    }

    public function store($id, Request $request)
    {
        //TODO required datetime oder date, mal gucken, obs klappt
        $this->validate($request,[
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'slots' => 'required|max:11',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description_public' => 'required|max:500',
            'description_private' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required|date|before:start',
            'cancelled' => 'required|max:1'
        ]);

        $meeting_series = new MeetingSeries;
        $meeting_series->docent_id = $id;
        $meeting_series->save();

        $meeting = new Meeting;
        $meeting->start = $request->get('start');
        $meeting->end = $request->get('end');
        $meeting->slots = $request->get('slots');
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description_public = $request->get('description_public');
        $meeting->description_private = $request->get('description_private');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
        $meeting->cancelled = $request->get('cancelled');
        $meeting->meeting_series_id = $meeting_series->id;
        $meeting->save();

        return redirect('/docent/' . $id . '/meeting');
    }

    public function update($id, Request $request, Meeting $meeting)
    {
        $this->validate($request,[
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'slots' => 'required|max:11',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description_public' => 'required|max:500',
            'description_private' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required|date|before:start',
            'cancelled' => 'required|max:1'
        ]);

        $meeting->start = $request->get('start');
        $meeting->end = $request->get('end');
        $meeting->slots = $request->get('slots');
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description_public = $request->get('description_public');
        $meeting->description_private = $request->get('description_private');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
        $meeting->cancelled = $request->get('cancelled');
        $meeting->save();

        return redirect('/docent/' . $id . '/meeting');
    }
}
