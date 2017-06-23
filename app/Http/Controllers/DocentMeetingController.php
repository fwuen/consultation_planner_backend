<?php

namespace App\Http\Controllers;

use App\Participation;
use App\Student;
use App\StudentNotification;
use Illuminate\Database\Eloquent\Collection;
use App\Meeting;
use App\MeetingSeries;
use Illuminate\Http\Request;

//TODO beim Erstellen von Meetings überprüfen, dass entweder Max participations oder slots auf 1 steht
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

        foreach ($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)->get();
            foreach ($meetingsInSeries as $meeting) {
                $meeting->checkDates();
                $participations = Participation::where('meeting_id', '=', $meeting->id)->get();
                //$meeting->participations = $participations;
                foreach ($participations as $participation) {
                    $student = Student::findOrFail($participation->student_id);
                    $student->participation = $participation;
                    $students->add($student);
                }
                $meeting->students = $students;
                $students = new Collection();
                //$singleCoalition = new MeetingStudentCoalition($meeting, $students, $participations);
                //$students = new Collection();
                $meetingStudentCoalitions->push($meeting);
            }
            $meetings->add($meetingsInSeries);
        }

        return response()->json($meetingStudentCoalitions);
    }

    private function doBasicRequestValidation(Request $request)
    {
        $this->validate($request, [
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'slots' => 'required|max:11',
            'max_participants' => 'required|max:11',
            'email_notification_docent' => 'required|max:1',
            'title' => 'required|max:50',
            'description' => 'required|max:500',
            'room' => 'required|max:10',
            'last_enrollment' => 'required|date|before:start'
        ]);
    }

    public function store($id, Request $request)
    {
        $this->doBasicRequestValidation($request);

        $meetingSeries = new MeetingSeries;
        $meetingSeries->docent_id = $id;
        $meetingSeries->save();

        $meeting = new Meeting;
        $meeting->start = $request->get('start');
        $meeting->end = $request->get('end');
        $meeting->slots = $request->get('slots');
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description = $request->get('description');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
        $meeting->cancelled = 0;
        $meeting->meeting_series_id = $meetingSeries->id;
        $meeting->participants_count = 0;
        $meeting->has_passed = 0;

        //TODO das hier ist ungetestet!
        if ($meeting->slots != 1 && $meeting->max_participations != 1) {
            $meetingSeries->delete();
            return redirect('docent/' . $id . '/meeting');
        }

        $meeting->save();

        return redirect('docent/' . $id . '/meeting');
    }

    public function storeSeries($id, Request $request)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
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
            if ($meeting->slots != 1 && $meeting->max_participations != 1) {
                $meetingSeries->delete();
                return redirect('docent/' . $id . '/meeting');
            }
            $meeting->save();

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

        $meeting->start = $request->get('start');
        $meeting->end = $request->get('end');
        $meeting->slots = $request->get('slots');
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description = $request->get('description');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
        $meeting->cancelled = $request->get('cancelled');
        $meeting->save();

        $this->notifyRelevantStudents($meeting->id);

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
}