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
     * @return null | Redirector
     */
    public function login(Request $request)
    {
        // request to api
        $username = $request->get('username');
        $password = $request->get('password');

        $url = 'http://localhost:69/authenticate';
        $data = array('username' => $username, 'password' => $password);

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        try {
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result == 'Password wrong') {
                return response('No authorization', 401);
            }
        } catch (Exception $exception) {
            return response('No authorization', 401);
        }

        $data = json_decode($result, true);
        $first_name = $data['firstname'];
        $last_name = $data['lastname'];
        $email = $data['email'];
        $role = $data['role'];

        $token = $this->generateRandomString();
        //Überprüfen ob es schon einen eintag in der Usertabelle gibt
        $users = User::where('email', '=', $email)->get();
        $url = null;
        $id = null;
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
            if ($role == 'student') {
                $student = new Student();
                $student->firstname = $first_name;
                $student->lastname = $last_name;
                $student->email = $email;
                $student->save();

                $student_id = $this->getStudentID($email);
                // ggf anlegen
                $token .= 's' . $student_id;

                $user = new User();
                $user->email = $email;
                $user->token = $token;
                $user->save();
            } else {
                $docent = new Docent();
                $docent->firstname = $first_name;
                $docent->lastname = $last_name;
                $docent->email = $email;
                $docent->save();

                $docent_id = $this->getDocentID($email);
                // ggf anlegen
                $token .= 'd' . $docent_id;

                $user = new User();
                $user->email = $email;
                $user->token = $token;
                $user->save();
            }
        }
        // entscheiden auf welche uri geleitet werden muss ($role)
        $id = null;
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public
    function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public
    function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public
    function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public
    function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(User $user)
    {
        //
    }
}
