<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\Participation;
use App\Slot;
use Illuminate\Database\Eloquent\Collection;

class StudentMeetingController extends Controller
{
    public function show($id, Meeting $meeting)
    {
        $participations = Participation::where('student_id', '=', $id)->where('meeting_id', '=', $meeting->id)->get();
        $slots = new Collection();
        foreach ($participations as $participation) {
            $slots = Slot::where('participation_id', '=', $participation->id)->get();
        }
        $meeting->participations = $participations;
        $meeting->slots = $slots;
        if($meeting->has_passed != 1) {
            $meeting->checkDates();
        }
        return response()->json($meeting);
    }

    public function index($id)
    {
        $timeForComparison = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        $timeForComparison->format('Y-m-d H:i:s');
        $timeForComparison->modify('-14 day');
        $participations = Participation::where('student_id', '=', $id)->where('end', '>=', $timeForComparison->format('Y-m-d H:i:s'))->get();
        $meetings = new Collection();

        foreach($participations as $participation)
        {
            $meeting = Meeting::findOrFail($participation->meeting_id);
            $slots = Slot::where('participation_id', '=', $participation->id)->get();
            if($meeting->has_passed != 1)
            {
                $meeting->checkDates();
            }
            $meeting->slots = $slots;
            $meeting->participation = $participation;
            $meetings->add($meeting);
        }

        return response()->json($meetings);
    }
}
