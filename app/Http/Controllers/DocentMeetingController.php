<?php

namespace App\Http\Controllers;

use App\Docent;
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
    public function __construct()
    {
        $this->middleware('auth.token');
        $this->middleware('auth.route.docent', ['only' => ['store', 'storeSeries', 'update', 'cancelSeries']]);
    }

    public function show($id, Meeting $meeting)
    {
        if ($meeting->has_passed != 1) {
            $meeting->checkDates();
        }
        return response()->json($meeting);
    }

    public function indexWithStudents($id)
    {
        $timeForComparison = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        $timeForComparison->modify('-14 day');
        $meetingSeries = MeetingSeries::where('docent_id', '=', $id)->get();
        $meetings = new Collection();
        $students = new Collection();
        $meetingStudentCoalitions = new \Illuminate\Support\Collection();

        foreach ($meetingSeries as $series) {
            $meetingsInSeries = Meeting::where('meeting_series_id', '=', $series->id)
                ->where('end', '>=', $timeForComparison->format('Y-m-d H:i:s'))
                ->get();

            foreach ($meetingsInSeries as $meeting) {
                if ($meeting->has_passed != 1) {
                    $meeting->checkDates();
                }
                $participations = Participation::where('meeting_id', '=', $meeting->id)->get();

                foreach ($participations as $participation) {
                    $student = Student::findOrFail($participation->student_id);
                    $student->participation = $participation;

                    $slots = Slot::where('participation_id', '=', $participation->id)->where('occupied', '=', 1)->get();
                    $student->slots = $slots;

                    $students->add($student);
                }
                $slotsForMeeting = Slot::where('meeting_id', '=', $meeting->id)->where('occupied', '=', 0)->get();
                $meeting->unoccupiedSlots = $slotsForMeeting;

                $meeting->participating_students = $students;
                $students = new Collection();
                $meetingStudentCoalitions->push($meeting);

            }
            $meetings->add($meetingsInSeries);
        }
        return response()->json($meetingStudentCoalitions);

    }

    public function store($id, Request $request)
    {
        $this->validate($request, [
            'is_series' => 'required|max:1'
        ]);
        if ($request->is_series == 1) {
            return $this->storeSeries($id, $request);
        } else {
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
                return;
            }
            $meeting->save();
            if($meeting->slots != 1) {
                $this->saveSlotsForMeeting($meeting, $request);
            }
        }
    }

    private function storeSeries($id, Request $request)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
            'slots' => 'required|max:11',
            'series_count' => 'required',
            'series_interval' => 'required'
        ]);

        $numberOfMeetings = $request->get('series_count');
        $interval = $request->get('series_interval');

        $meetingSeries = new MeetingSeries;
        $meetingSeries->docent_id = $id;
        $meetingSeries->save();

        $dateTimeForMeetingStart = new \DateTime($request->get('start'));
        $dateTimeForMeetingStart->format('Y-m-d H:i:s');
        $dateTimeForMeetingStart->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dateTimeForMeetingEnd = new \DateTime($request->get('end'), new \DateTimeZone("Europe/Berlin"));
        $dateTimeForMeetingEnd->format('Y-m-d H:i:s');
        $dateTimeForMeetingEnd->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dateTimeForMeetingLastEnrollment = new \DateTime($request->get('last_enrollment'), new \DateTimeZone("Europe/Berlin"));
        $dateTimeForMeetingLastEnrollment->format('Y-m-d H:i:s');
        $dateTimeForMeetingLastEnrollment->setTimezone(new \DateTimeZone('Europe/Berlin'));

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
                return;
            }
            $meeting->save();

            if($meeting->slots != 1) {
                $this->saveSlotsForMeeting($meeting, $request);
            }

            $dateTimeForMeetingStart->modify('+' . $interval . 'day');
            $dateTimeForMeetingEnd->modify('+' . $interval . 'day');
            $dateTimeForMeetingLastEnrollment->modify('+' . $interval . 'day');
        }
    }

    public function update($id, Request $request, Meeting $meeting)
    {
        $this->doBasicRequestValidation($request);
        $this->validate($request, [
            'cancelled' => 'max:1'
        ]);
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description = $request->get('description');
        $meeting->room = $request->get('room');
        if ($request->get('cancelled') != null) {
            $meeting->cancelled = $request->get('cancelled');
        }
        $meeting->save();

        $this->notifyRelevantStudents($id, $meeting);
    }

    public function cancelSeries($id, $idOfFirstMeeting)
    {
        $firstMeeting = Meeting::findOrFail($idOfFirstMeeting);
        $meetingsInSeries = Meeting::where('meeting_series_id', '=', $firstMeeting->meeting_series_id)->get();
        foreach ($meetingsInSeries as $meeting) {
            $meeting->cancelled = 1;
            $meeting->save();
            $this->notifyRelevantStudents($id, $meeting);
        }
    }

    private function notifyRelevantStudents($docentId, $meeting)
    {
        $docent = Docent::findOrFail($docentId);
        $participations = Participation::where('meeting_id', '=', $meeting->id)->get();

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

        $content = [
            'meetingTitle' => $meeting->title,
            'docentName' => $docent->firstname . ' ' . $docent->lastname
        ];
        foreach ($students as $student) {

            \Mail::send('notify.meeting.update', $content, function ($m) use ($student) {
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
        $dateTimeForMeetingStart = new \DateTime($request->get('start'));
        $dateTimeForMeetingStart->format('Y-m-d H:i:s');
        $dateTimeForMeetingStart->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dateTimeForMeetingEnd = new \DateTime($request->get('end'), new \DateTimeZone("Europe/Berlin"));
        $dateTimeForMeetingEnd->format('Y-m-d H:i:s');
        $dateTimeForMeetingEnd->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dateTimeForMeetingLastEnrollment = new \DateTime($request->get('last_enrollment'), new \DateTimeZone("Europe/Berlin"));
        $dateTimeForMeetingLastEnrollment->format('Y-m-d H:i:s');
        $dateTimeForMeetingLastEnrollment->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $meeting->start = $dateTimeForMeetingStart;
        $meeting->end = $dateTimeForMeetingEnd;
        $meeting->max_participants = $request->get('max_participants');
        $meeting->email_notification_docent = $request->get('email_notification_docent');
        $meeting->title = $request->get('title');
        $meeting->description = $request->get('description');
        $meeting->room = $request->get('room');
        $meeting->last_enrollment = $dateTimeForMeetingLastEnrollment;
    }

    private function saveSlotsForMeeting(Meeting $meeting, Request $request)
    {
        $start_time = clone $meeting->start;
        $end_time = clone $meeting->end;
        $start_time->format('Y-m-d H:i:s');
        $end_time->format('Y-m-d H:i:s');
        $start_time->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $end_time->setTimezone(new \DateTimeZone('Europe/Berlin'));

        $diff = $end_time->getTimestamp() - $start_time->getTimestamp();
        $mins = $diff / 60;

        $slot_interval = floor($mins / $request->slots);

        $check_time = clone $start_time;

        $add_time = clone $start_time;
        for ($i = 0; $i < $request->slots; $i++) {
            $slot = new Slot;
            $slot->meeting_id = $meeting->id;
            $slot->occupied = 0;
            $slot->start = $start_time;
            $check_time->add(new DateInterval("PT" . ($slot_interval * 2) . "M"));
            if ($check_time > $end_time) {
                $slot->end = $end_time;
            } else {
                $slot->end = $add_time->add(new DateInterval("PT" . $slot_interval . "M"));;
            }
            $slot->save();
            $start_time->add(new DateInterval("PT" . $slot_interval . "M"));
            $check_time = clone $start_time;
        }
    }
}