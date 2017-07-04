<?php

namespace App\Http\Controllers;

use App\Docent;
use App\DocentNotification;
use App\Meeting;
use App\MeetingSeries;
use App\Participation;
use App\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentParticipationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.token');
        $this->middleware('auth.route.student', ['only' => ['store', 'destroy']]);
    }

    public function show($id, Participation $participation)
    {
        return response()->json($participation);
    }

    public function index($id)
    {
        $timeForComparison = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        $timeForComparison->modify('-14 day');
        $participations = Participation::where('student_id', '=', $id)->where('end', '>=', $timeForComparison->format('Y-m-d H:i:s'))->get();
        $participationResults = new Collection();
        foreach ($participations as $participation) {
            $meeting = Meeting::findOrFail($participation->meeting_id);
            if($meeting->has_passed != 1)
            {
                $meeting->checkDates();
            }
            $meetingSeries = MeetingSeries::findOrFail($meeting->meeting_series_id);
            $docent = Docent::findOrFail($meetingSeries->docent_id);
            $meeting->participation = $participation;
            $meeting->docent = $docent;
            $participationResults->push($meeting);
        }
        return response()->json($participationResults);
    }

    public function store($id, Request $request)
    {
        $this->doBasicParticipationValidation($request);

        $participation = new Participation();
        $this->setParticipationProperties($participation, $request, $id);
        $meeting = Meeting::findOrFail($participation->meeting_id);

        if ($meeting->slots == 1) {
            $meeting->participants_count = $meeting->participants_count + 1;
            if ($meeting->participants_count <= $meeting->max_participants) {
                $participation->save();
                $meeting->save();
            }
        } else if ($meeting->slots > 1) {
            $participation->save();
            $slot = Slot::findOrFail($request->slot_id);
            if($slot->occupied == 1) {
                return;
            }
            $slot->participation_id = $participation->id;
            $slot->occupied = 1;
            $slot->save();
        }

        $this->notifyRelevantDocent($participation, 'store');
    }

    public function destroy($id, Participation $participation)
    {
        $meeting = Meeting::findOrFail($participation->meeting_id);
        //TODO das hier ist noch ungetestet
        if ($meeting->slots == 1) {
            $meeting->participants_count = $meeting->participants_count - 1;
            $meeting->save();
        }
        $slots = Slot::where('participation_id', '=', $participation->id)->get();
        foreach ($slots as $slot) {
            $slot->participation_id = null;
            $slot->occupied = 0;
            $slot->save();
        }
        $this->notifyRelevantDocent($participation, 'delete');
        $participation->delete();
    }

    private function notifyRelevantDocent(Participation $participation, $typeOfNotification)
    {
        $meeting = Meeting::findOrFail($participation->meeting_id);
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
        $docentNotification->meeting_id = $meeting->id;
        $docentNotification->seen = 0;
        $docentNotification->save();

        if ($meeting->email_notification_docent == 1) {
            $data = [
                'meetingTitle' => $meeting->title,
                'partStart' => $participation->start,
                'partRemark' => $participation->remark
            ];
            switch ($typeOfNotification) {
                case 'store':
                    \Mail::send('notify.meeting.newparticipation', $data, function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Neue Anmeldung zu Ihrer Sprechstunde');
                    });
                    break;
                case 'update':
                    \Mail::send('notify.meeting.updateparticipation', $data, function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Geänderte Anmeldung zu Ihrer Sprechstunde');
                    });
                    break;
                case 'delete':
                    \Mail::send('notify.meeting.deleteparticipation', $data, function ($m) use ($docent) {
                        $m->to($docent->email)->subject('Abmeldung von Ihrer Sprechstunde');
                    });
                    break;
            }
        }
    }

    private function doBasicParticipationValidation(Request $request)
    {
        $this->validate($request, [
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'student_id' => 'required|max:10',
            'meeting_id' => 'required|max:10',
            'email_notification_student' => 'required'
        ]);
    }

    private function setParticipationProperties(Participation $participation, Request $request, $id)
    {
        $dateTimeForStart = new \DateTime($request->get('start'), new \DateTimeZone("Europe/Berlin"));
        $dateTimeForEnd = new \DateTime($request->get('end'), new \DateTimeZone("Europe/Berlin"));
        $participation->start = $dateTimeForStart->format('Y-m-d H:i:s');
        $participation->end = $dateTimeForEnd->format('Y-m-d H:i:s');
        $participation->student_id = $request->get('student_id');
        $participation->meeting_id = $request->get('meeting_id');
        $participation->remark = $request->get('remark');
        $participation->email_notification_student = $request->get('email_notification_student');

    }


}