<?php

namespace App\Http\Controllers;

use App\Docent;
use App\Meeting;
use App\MeetingSeries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

//TODO: was passiert, wenn die Validierung ergibt, dass die Daten nicht korrekt sind? --> irgendwie behandeln?
class DocentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255',
            'academic_title' => 'required|max:50'
        ]);

        $docent = new Docent;
        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');

        $docent->save();

        return redirect('/docent/'.$docent->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function show(Docent $docent)
    {
        return response()->json($docent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Docent $docent)
    {
        //Analog zu store()
        $this->validate($request,[
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255',
            'academic_title' => 'required|max:50'
        ]);

        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');

        $docent->save();
        return redirect('/docent/'.$docent->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Docent $docent)
    {
        //TODO: Redirect eventuell überarbeiten bzw. ist dieser überhaupt nötig? --> muss in diesem Fall auch in allen anderen Ressource-Controllern geändert werden
        $docent->delete();
        return redirect()->route('/');
    }

    /**
     * Search for specific resources by term.
     *
     * @param  String  $term
     * @return \Illuminate\Support\Collection $docents
     */
    public function search($term)
    {
        $termArray = explode(" ", $term);

        $docents = \DB::table('docents')
            ->where(function ($query) use($termArray) {
                foreach ($termArray as $value) {
                    $query->orWhere('lastname', 'like', '%'.$value.'%');
                    $query->orWhere('firstname', 'like', '%'.$value.'%');
                }
            })->get();

        return response()->json($docents);
    }

    public function getMeetingsByDocent($id)
    {
        $meeting_series = \App\MeetingSeries::where('docent_id', '=', $id)->get();

        $meetings = new Collection();

        foreach($meeting_series as $series) {
            $meetings_in_series = \App\Meeting::where('meeting_series_id', '=', $series->id)->get();
            $meetings->add($meetings_in_series);
        }
        return response()->json($meetings);
    }

    public function storeMeeting(Request $request, $id)
    {
        //TODO required datetime oder date
        $this->validate($request,[
            'start' => 'required',
            'end' => 'required',
            'slots' => 'required|max:11',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description_public' => 'required|max:500',
            'description_private' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required',
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

        return redirect('/docent/'.$id.'/meeting');
    }

    public function updateMeeting(Request $request, $id)
    {
        $this->validate($request,[
            'start' => 'required',
            'end' => 'required',
            'slots' => 'required|max:11',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description_public' => 'required|max:500',
            'description_private' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required',
            'cancelled' => 'required|max:1'
        ]);

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
        $meeting->save();

        return redirect('/docent/'.$id.'/meeting');
    }

    public function getNotificationsByDocent($id)
    {
        $docent_notifications = \App\DocentNotification::where('docent_id', '=', $id)->get();
        return response()->json($docent_notifications);
    }
}
