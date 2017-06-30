<?php

namespace App\Http\Controllers;

use App\Docent;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Mockery\Exception;

class UserController extends Controller
{
    /**
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        \DB::table('users')->where('token', $token)->update(['token' => "logged out"]);
    }

    /**
     *
     * @param Request $request
     * @return null | Redirector
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        // request to api
        $result = $this->sendRequest($username, $password);

        if ($result == null) {
            return response('No authorization', 401);
        }
        // extract data
        $data = json_decode($result, true);
        $first_name = $data['firstname'];
        $last_name = $data['lastname'];
        $email = $data['email'];
        $role = $data['role'];

        $url = null;
        $id = null;

        $token = $this->generateRandomString();
        //Überprüfen ob es schon einen Eintrag in der Usertabelle gibt
        $users = User::where('email', '=', $email)->get();
        if ($users->contains('email', $email)) {
            $id = $role == 'student' ? $this->getStudentID($email) : $this->getDocentID($email);
            if ($role == 'student') {
                $token = $this->generateRandomString() . 's' . $id;
                \DB::table('users')->where('email', $email)->update(['token' => $token]);
            } else {
                $token = $this->generateRandomString() . 'd' . $id;
                \DB::table('users')->where('email', $email)->update(['token' => $token]);
            }
            \DB::table('users')->where('email', $email)->update(['updated_at' => date('Y-m-d G:i:s')]);
        } else {
            //neuen Eintrag anlegen
            if ($role == 'student') {
                $this->createStudent($first_name, $last_name, $email);
                $student_id = $this->getStudentID($email);
                $token .= 's' . $student_id;
                $this->createUser($email, $token);
            } else {
                $this->createDocent($first_name, $last_name, $email);
                $docent_id = $this->getDocentID($email);
                $token .= 'd' . $docent_id;
                $this->createUser($email, $token);
            }
        }
        // entscheiden auf welche uri geleitet werden muss ($role)
        if ($role == 'student') {
            $id = $this->getStudentID($email);
            if ($id == null) {
                return response('No authorization', 401);
            }
            $url = '/student/' . $id . '/meeting';
        } else {
            $id = $this->getDocentID($email);
            if ($id == null) {
                return response('No authorization', 401);
            }
            $url = '/docent/' . $id . '/meeting';
        }
        if ($url != null) {
            return redirect($url)->header('Authorization', $token);
        }
        return response('No authorization', 401);
    }

    /**
     * @param $email
     * @param $token
     */
    public function createUser($email, $token)
    {
        $user = new User();
        $user->email = $email;
        $user->token = $token;
        $user->save();
    }

    /**
     * @param $first_name
     * @param $last_name
     * @param $email
     */
    public function createStudent($first_name, $last_name, $email)
    {
        $student = new Student();
        $student->firstname = $first_name;
        $student->lastname = $last_name;
        $student->email = $email;
        $student->save();
    }

    /**
     * @param $first_name
     * @param $last_name
     * @param $email
     */
    public function createDocent($first_name, $last_name, $email)
    {
        $docent = new Docent();
        $docent->firstname = $first_name;
        $docent->lastname = $last_name;
        $docent->email = $email;
        $docent->save();
    }


    /**
     * @param $username
     * @param $password
     * @return bool|null|string
     */
    public function sendRequest($username, $password)
    {
        try {
            $url = 'http://localhost:69/authenticate';
            $data = array('username' => $username, 'password' => $password);

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);

        } catch (Exception $exception) {
            return null;
        }
        if ($result == 'Password wrong' || $result == false) {
            return null;
        }

        return $result;
    }

    /**
     * @param String $email
     * @return null
     */
    public function getStudentID($email)
    {
        try {
            $students = Student::where('email', '=', $email)->get();
            $id = null;
            foreach ($students as $item) {
                $id = $item->id;
            }
            return $id;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param $email
     * @return null
     */
    public function getDocentID($email)
    {
        try {
            $docents = Docent::where('email', '=', $email)->get();
            $id = null;
            foreach ($docents as $item) {
                $id = $item->id;
            }
            return $id;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     *
     * @param int $length
     * @return bool|string
     */
    function generateRandomString($length = 50)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
