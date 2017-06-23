<?php

namespace App\Http\Controllers;

use App\Docent;
use App\DocentNotification;
use App\Meeting;
use App\MeetingSeries;
use App\Participation;
use Illuminate\Http\Request;

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
        $this->doBasicParticipationValidation($request);

        $participation = new Participation();
        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');
        $participation->email_notification_student = $request->get('email_notification_student');

        $meeting = Meeting::findOrFail($participation->meeting_id);

        //hier wird sich darauf verlassen, dass vom Frontend nur zulässige/nicht belegte Dates kommen
        //TODO das hier ist ugnetestet!
        if($meeting->slots == 1) {
            $meeting->participants_count = $meeting->participants_count + 1;
            if($meeting->participants_count <= $meeting->max_participants) {
                $participation->save();
                $meeting->save();
            }
        } else if($meeting->slots > 1) {
            $participation->save();
        }

        $this->notifyRelevantDocent($participation->meeting_id, 'store');
        return redirect('student/' . $id . '/participation');
    }

    public function update($id, Request $request, Participation $participation)
    {
        $this->doBasicParticipationValidation($request);

        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->start = $request->get('start');
        $participation->end = $request->get('end');
        $participation->email_notification_student = $request->get('email_notification_student');
        $participation->save();

        $this->notifyRelevantDocent($participation->meeting_id, 'update');

        return redirect('student/' . $id . '/participation');
    }

    public function destroy($id, Participation $participation)
    {
        $meeting = Meeting::findOrFail($participation->meeting_id);
        //TODO das hier ist noch ungetestet
        if($meeting->slots == 1) {
            $meeting->participants_count = $meeting->participants_count - 1;
            $meeting->save();
        }
        $meeting->participants_count = $meeting->participants_count - 1;
        $meeting->save();
        $this->notifyRelevantDocent($participation->meeting_id, 'delete');
        $participation->delete();
        return redirect('student/' . $id . '/participation');
    }

    private function notifyRelevantDocent($meetingId, $typeOfNotification)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $meetingSeries = MeetingSeries::findOrFail($meeting->meeting_series_id);
        $docent = Docent::findOrFail($meetingSeries->docent_id);

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
            switch($typeOfNotification) {
                case 'store':
                    \Mail::send('notify.meeting.newparticipation', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Neue Anmeldung zu Ihrer Sprechstunde');
                    });
                    break;
                case 'update':
                    \Mail::send('notify.meeting.updateparticipation', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Geänderte Anmeldung zu Ihrer Sprechstunde');
                    });
                    break;
                case 'delete':
                    \Mail::send('notify.meeting.deleteparticipation', ['docent' => $docent], function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Abmeldung von Ihrer Sprechstunde');
                    });
                    break;
            }
        }
    }

    private function doBasicParticipationValidation(Request $request)
    {
        $this->validate($request,[
            'student_id' => 'required|max:10',
            'meeting_id' => 'required|max:10',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'email_notification_student' => 'required'
        ]);
    }
}