<?php

namespace App\Http\Controllers;
use App\MeetingStudentCoalition;
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
        if($meeting->has_passed != 1) {
            $meeting->checkDates();
        }
        return response()->json($meeting);
    }

    public function index($id) {
        $meetingSeries = MeetingSeries::where('docent_id', '=', $id)->get();
        $meetings = new Collection();

        foreach($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)->get();
            //TODO prüfen, ob hier anschließend die aktuellen Meetings oder veraltete zurückgegeben werden, da  man sich die Meetings nach dem Update nicht nochmal explizit holt!
            foreach($meetingsInSeries as $meeting) {
                $meeting->checkDates();
            }
            $meetings->add($meetingsInSeries);
        }

        return response()->json($meetings);
    }

    public function indexWithStudents($id) {
        $meetingSeries = MeetingSeries::where('docent_id', '=', $id)->get();
        $meetings = new Collection();
        $students = new Collection();
        $meetingStudentCoalitions = new \Illuminate\Support\Collection();

        foreach($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)->get();
            foreach($meetingsInSeries as $meeting) {
                $meeting->checkDates();
                $participations = Participation::where('meeting_id', '=', $meeting->id)->get();
                $meeting->participations = $participations;
                foreach($participations as $participation) {
                    $students->add(Student::findOrFail($participation->student_id));
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

    public function store($id, Request $request)
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
            'last_enrollment' => 'required|date|before:start'
        ]);

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
        $meeting->description_public = $request->get('description_public');
        $meeting->description_private = $request->get('description_private');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $request->get('last_enrollment');
        $meeting->cancelled = 0;
        $meeting->meeting_series_id = $meetingSeries->id;
        $meeting->participants_count = 0;
        $meeting->has_passed = 0;

        //TODO das hier ist ungetestet!
        if($meeting->slots != 1 && $meeting->max_participations != 1)
        {
            $meetingSeries->delete();
            return redirect('docent/' . $id . '/meeting');
        }

        $meeting->save();

        return redirect('docent/' . $id . '/meeting');
    }

    public function storeSeries($id, Request $request)
    {
        $numberOfMeetings = $request->count;

        $meetingSeries = new MeetingSeries;
        $meetingSeries->docent_id = $id;
        $meetingSeries->save();

        $this->validate($request, [
            'meetings.*.start' => 'required|date',
            'meetings.*.end' => 'required|date|after:meetings.*.start',
            'meetings.*.max_participants' => 'required|max:11',
            'meetings.*.email_notification_docent' => 'required|max:1',
            'meetings.*.title' => 'required|max:50',
            'meetings.*.description_public' => 'required|max:500',
            'meetings.*.description_private' => 'required|max:500',
            'meetings.*.room' => 'required|max:10',
            'meetings.*.last_enrollment' => 'required|date|before:meetings.*.start',
            'meetings.*.slots' => 'required|max:11',
            'count' => 'required'
        ]);

        for($i = 0; $i < $numberOfMeetings; $i++)
        {
            $meeting = new Meeting;
            $meeting->start = $request->input('meetings.' . $i . '.start');
            $meeting->end = $request->input('meetings.' . $i . '.end');
            $meeting->slots = $request->input('meetings.' . $i . '.slots');
            $meeting->max_participants = $request->input('meetings.' . $i . '.max_participants');
            $meeting->email_notification_docent = $request->input('meetings.' . $i . '.email_notification_docent');
            $meeting->title = $request->input('meetings.' . $i . '.title');
            $meeting->description_public = $request->input('meetings.' . $i . '.description_public');
            $meeting->description_private = $request->input('meetings.' . $i . '.description_private');
            $meeting->room = $request->input('meetings.' . $i . '.room');
            $meeting->last_enrollment = $request->input('meetings.' . $i . '.last_enrollment');
            $meeting->cancelled = 0;
            $meeting->meeting_series_id = $meetingSeries->id;
            $meeting->participants_count = 0;
            $meeting->has_passed = 0;
            //TODO das hier ist ungetestet!
            if($meeting->slots != 1 && $meeting->max_participations != 1)
            {
                $meetingSeries->delete();
                return redirect('docent/' . $id . '/meeting');
            }

            $meeting->save();
        }


        return redirect('docent/' . $id . '/meeting');
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

        $this->notifyRelevantStudents($meeting->id);

        return redirect('docent/' . $id . '/meeting');
    }

    private function notifyRelevantStudents($meetingId)
    {
        $participations = Participation::where('meeting_id', '=', $meetingId)->get();

        $students = new Collection();
        foreach ($participations as $participation)
        {
            if($participation->email_notification_student == 1)
            {
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

        foreach ($students as $student)
        {
            \Mail::send('notify.meeting.update', ['student' => $student], function ($m) use ($student) {
                $m->to($student->email)->subject('Aktualisierung einer Sprechstunde');
            });
        }

    }
}