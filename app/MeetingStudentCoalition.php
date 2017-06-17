<?php

namespace App;


class MeetingStudentCoalition
{
    public $meeting;
    public $students;
    public $participations;

    public function __construct($meeting, $students, $participations)
    {
        $this->meeting = $meeting;
        $this->students = $students;
        $this->participations = $participations;
    }

    public function setMeeting($meeting)
    {
        $this->meeting = $meeting;
    }

    public function setStudents($students)
    {
        $this->students = $students;
    }

    public function getStudents() {
        return $this->students;
    }

    public function getMeeting() {
        return $this->meeting;
    }
}
