<?php

namespace App\Http\Controllers;

use App\Participation;
use App\Slot;
use App\Student;
use App\StudentNotification;
use DateInterval;
use Illuminate\Database\Eloquent\Collection;
use App\Meeting;
use App\MeetingSeries;
use Illuminate\Http\Request;

class DocentMeetingController extends Controller
{
    public function show($id, Meeting $meeting)
    {
        if ($meeting->has_passed != 1) {
            $meeting->checkDates();
        }
        return response()->json($meeting);
    }

    public function index($id)
    {
        $meetingSeries = MeetingSeries::where('docent_id', '=', $id)->get();
        $meetings = new Collection();

        foreach ($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)->get();
            foreach ($meetingsInSeries as $meeting) {
                if($meeting->has_passed != 1) {
                    $meeting->checkDates();
                }

            }
            $meetings->add($meetingsInSeries);
        }

        return response()->json($meetings);
    }

    public function indexWithStudents($id)
    {
        $meetingSeries = MeetingSeries::where('docent_id', '=', $id)->get();
        $meetings = new Collection();
        $students = new Collection();
        $meetingStudentCoalitions = new \Illuminate\Support\Collection();

        // alle Meetings einer Serie sammeln
        foreach ($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)->get();

            // jedes gefundene Meeting behandeln
            foreach ($meetingsInSeries as $meeting) {
                if ($meeting->has_passed != 1) {
                    $meeting->checkDates();
                }
                // alle Participations für das gefundene Meeting holen
                $participations = Participation::where('meeting_id', '=', $meeting->id)->get();

                // anhand der gefundenen Participations die teilnehmenden Studenten ermitteln
                foreach ($participations as $participation) {
                    $student = Student::findOrFail($participation->student_id);
                    $student->participation = $participation;

                    $slots = Slot::where('participation_id', '=', $participation->id)->where('occupied', '=', 1)->get();
                    $student->slots = $slots;

                    $students->add($student);
                }
                // alle nicht belegten Slots behandeln
                $slotsForMeeting = Slot::where('meeting_id', '=', $meeting->id)->where('occupied', '=', 0)->get();
                $meeting->unoccupiedSlots = $slotsForMeeting;

                $meeting->students = $students;
                $students = new Collection();
                $meetingStudentCoalitions->push($meeting);

            }
            $meetings->add($meetingsInSeries);
        }
        return response()->json($meetingStudentCoalitions);

    }

    public function store($id, Request $request)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
            'slots' => 'required|max:11'
        ]);

        $meetingSeries = new MeetingSeries;
        $meetingSeries->docent_id = $id;
        $meetingSeries->save();

        $meeting = new Meeting;
        $this->setMeetingProperties($meeting, $request);
        $meeting->slots = $request->slots;
        $meeting->cancelled = 0;
        $meeting->meeting_series_id = $meetingSeries->id;
        $meeting->participants_count = 0;
        $meeting->has_passed = 0;

        //TODO das hier ist ungetestet!!!!!!!
        if ($meeting->slots != 1 && $meeting->max_participants != 1) {
            $meetingSeries->delete();
            return redirect('docent/' . $id . '/meeting');
        }
        $meeting->save();

        $this->saveSlotsForMeeting($meeting, $request);

        return redirect('docent/' . $id . '/meeting');
    }

    public function storeSeries($id, Request $request)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
            'slots' => 'required|max:11',
            'count' => 'required',
            'interval' => 'required'
        ]);

        $numberOfMeetings = $request->get('count');
        $interval = $request->get('interval');

        $meetingSeries = new MeetingSeries;
        $meetingSeries->docent_id = $id;
        $meetingSeries->save();

        $dateTimeForMeetingStart = new \DateTime($request->get('start'));
        $dateTimeForMeetingEnd = new \DateTime($request->get('end'));
        $dateTimeForMeetingLastEnrollment = new \DateTime($request->get('last_enrollment'));

        for ($i = 0; $i < $numberOfMeetings; ++$i) {

            $meeting = new Meeting;
            $meeting->start = $dateTimeForMeetingStart;
            $meeting->end = $dateTimeForMeetingEnd;
            $meeting->slots = $request->get('slots');
            $meeting->max_participants = $request->get('max_participants');
            $meeting->email_notification_docent = $request->get('email_notification_docent');
            $meeting->title = $request->get('title');
            $meeting->description = $request->get('description');
            $meeting->room = $request->get('room');
            $meeting->last_enrollment = $dateTimeForMeetingLastEnrollment;
            $meeting->cancelled = 0;
            $meeting->meeting_series_id = $meetingSeries->id;
            $meeting->participants_count = 0;
            $meeting->has_passed = 0;

            //TODO das hier ist ungetestet!
            if ($meeting->slots != 1 && $meeting->max_participants != 1) {
                $meetingSeries->delete();
                return redirect('docent/' . $id . '/meeting');
            }
            $meeting->save();

            $this->saveSlotsForMeeting($meeting, $request);

            $dateTimeForMeetingStart->modify('+' . $interval . 'day');
            $dateTimeForMeetingEnd->modify('+' . $interval . 'day');
            $dateTimeForMeetingLastEnrollment->modify('+' . $interval . 'day');
        }

        return redirect('docent/' . $id . '/meeting');
    }

    public function update($id, Request $request, Meeting $meeting)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
            'cancelled' => 'required|max:1'
        ]);
        $this->setMeetingProperties($meeting, $request);
        $meeting->cancelled = $request->get('cancelled');
        $meeting->save();

        $this->notifyRelevantStudents($meeting->id);

        return redirect('docent/' . $id . '/meeting');
    }

    public function cancelSeries($id, $idOfFirstMeeting)
    {
        $firstMeeting = Meeting::findOrFail($idOfFirstMeeting);
        $meetingsInSeries = Meeting::where('meeting_series_id', '=', $firstMeeting->meeting_series_id)->get();
        foreach ($meetingsInSeries as $meeting)
        {
            $meeting->cancelled = 1;
            $meeting->save();
            $this->notifyRelevantStudents($meeting->id);
        }
        return redirect('docent/' . $id . '/meeting');
    }

    private function notifyRelevantStudents($meetingId)
    {
        $participations = Participation::where('meeting_id', '=', $meetingId)->get();

        $students = new Collection();
        foreach ($participations as $participation) {
            if ($participation->email_notification_student == 1) {
                $students->push(Student::findOrFail($participation->student_id));
            }
            //TODO Verweise auf notification message mit den richtigen IDs versehen
            $studentNotification = new StudentNotification();
            $studentNotification->message_id = 4;
            $studentNotification->participation_id = $participation->id;
            $studentNotification->student_id = $participation->student_id;
            $studentNotification->seen = 0;
            $studentNotification->save();
        }

        foreach ($students as $student) {
            \Mail::send('notify.meeting.update', ['student' => $student], function ($m) use ($student) {
                $m->to($student->email)->subject('Aktualisierung einer Sprechstunde');
            });
        }

    }

    private function doBasicRequestValidation(Request $request)
    {
        $this->validate($request, [
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required|date|before:start'
        ]);
    }

    private function setMeetingProperties(Meeting $meeting, Request $request)
    {
        $meeting->start = $request->get('start');
        $meeting->end = $request->get('end');
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description = $request->get('description');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
    }

    private function saveSlotsForMeeting(Meeting $meeting, Request $request)
    {
        $start_time = new  \DateTime($request->start);
        $end_time = new \DateTime($request->end);

        $diff = $end_time->getTimestamp() - $start_time->getTimestamp();
        $mins = $diff/60;

        $slot_interval = floor($mins/$request->slots);

        // Zeit, mit der überprüft wird, ob beim aktuellen Meeting bis zum Ende aufgefüllt werden muss
        $check_time = clone $start_time;

        // Zeit, die genutzt wird, um die Endzeit des Meetings festzulegen, wenn nicht aufgefüllt werden muss
        $add_time = clone $start_time;
        for($i = 0; $i < $request->slots; $i++) {
            $slot = new Slot;
            $slot->meeting_id = $meeting->id;
            $slot->occupied = 0;
            $slot->start = $start_time;
            $check_time->add(new DateInterval("PT" . ($slot_interval*2) . "M"));
            if ($check_time > $end_time) {
                $slot->end = $end_time;
            } else {
                $slot->end = $add_time->add(new DateInterval("PT" . $slot_interval . "M"));;
            }
            $slot->save();
            $start_time->add(new DateInterval("PT" . $slot_interval . "M"));
            // check_time auf den nächsten Wert von start_time setzen, dass von ihm aus überprüft werden kann
            $check_time = clone $start_time;
        }
    }
}