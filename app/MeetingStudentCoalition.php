<?php

namespace App;

class MeetingStudentCoalition
{
    private $meeting;
    private $students;

    public function __construct($meeting, $students)
    {
        $this->meeting = $meeting;
        $this->students = $students;
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
