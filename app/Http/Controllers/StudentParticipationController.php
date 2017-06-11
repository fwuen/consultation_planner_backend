<?php

namespace App\Http\Controllers;

use App\Docent;
use App\DocentNotification;
use App\Meeting;
use App\MeetingSeries;
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
        $meetings = Participation::where('student_id', '=', $id)->get();
        return response()->json($meetings);
    }

    public function store($id, Request $request)
    {
        //TODO datetime validate
        $this->validate($request,[
            'student_id' => 'required|max:10',
            'meeting_id' => 'required|max:10',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'email_notification_student' => 'required'
        ]);

        $participation = new Participation();
        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');

        $this->notifyRelevantDocent($participation->meeting_id, 'store');

        return redirect('student/' . $id . '/participation');
    }

    public function update($id, Request $request, Participation $participation)
    {
        $this->validate($request,[
            'student_id' => 'required|max:10',
            'meeting_id' => 'required|max:10',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'email_notification_student' => 'required'
        ]);

        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');
        $participation->save();

        $this->notifyRelevantDocent($participation->meeting_id, 'update');

        return redirect('student/' . $id . '/participation');
    }

    public function destroy($id, Participation $participation)
    {
        $participation->delete();
        $this->notifyRelevantDocent($participation->meeting_id, 'delete');
        return redirect('student/' . $id . '/participation');
    }

    private function notifyRelevantDocent($meetingId, $typeOfNotification)
    {
        $meeting = Meeting::find($meetingId);
        $meetingSeries = MeetingSeries::find($meeting->meeting_series_id);
        $docent = Docent::find($meetingSeries->docent_id);

        $docentNotification = new DocentNotification();
        switch ($typeOfNotification) {
            case 'store':
                $docentNotification->message_id = 1;
                break;
            case 'update':
                $docentNotification->message_id = 2;
                break;
            case 'delete':
                $docentNotification->message_id = 3;
                break;
        }
        $docentNotification->docent_id = $docent->id;
        $docentNotification->meeting_id = $meetingId;
        $docentNotification->seen = 0;
        $docentNotification->save();

        if($meeting->email_notification_docent == 1)
        {
            //TODO views für e-mail benachrichtigung
            switch($typeOfNotification) {
                case 'store':
                    \Mail::send('welcome', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Neue Anmeldung für Ihre Sprechstunde');
                    });
                    break;
                case 'update':
                    \Mail::send('welcome', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Geänderte Anmeldung für Ihre Sprechstunde');
                    });
                    break;
                case 'delete':
                    \Mail::send('welcome', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Gelöschte Anmeldung für Ihre Sprechstunde');
                    });
                    break;
            }
        }
    }
}
