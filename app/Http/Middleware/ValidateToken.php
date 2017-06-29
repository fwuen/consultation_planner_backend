<?php

namespace App\Http\Middleware;

use App\User;
use App\Docent;
use App\Student;
use App\Http\Controllers\UserController;
use Closure;
use Mockery\Exception;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $value = $request->header('Authorization');
        $user_id = substr($value, 51);
        $role = substr($value, 50, 1);

        $users = \DB::table('users')->where('token', $value)->get();
        $email = null;
        foreach ($users as $user) {
            $last_refresh = strtotime($user->updated_at);
            $current_time = time();
            $difference = $current_time - $last_refresh;
            if ($difference < 3600) {
                \DB::table('users')->where('token', $value)->update(['updated_at' => date('Y-m-d G:i:s')]);
                $email = $user->email;
            } else {
                return response('No authorization', 401);
            }
        }

        if ($role == 's') {
            try {
                $student = Student::findOrFail($user_id);
                if ($student->email != $email) {
                    return response('No authorization', 401);
                }
            } catch (Exception $exception) {
                return response('No authorization', 401);
            }
        } else if ($role === 'd') {
            try {
                $docent = Docent::findOrFail($user_id);
                if ($docent->email != $email) {
                    return response('No authorization', 401);
                }
            } catch (Exception $exception) {
                return response('No authorization', 401);
            }
        } else {
            return response('No authorization', 401);
        }

        if ($request->getRequestUri() == '/') {
            $userController = new UserController();
            if ($role == 's') {
                $id = $userController->getStudentID($email);
                if ($id != null) {
                    return redirect('/student/' . $id . '/meeting');
                }

                return response('No authorization', 401);
            } else {
                $id = $userController->getDocentID($email);
                if ($id != null) {
                    return redirect('/docent/' . $id . '/meeting');
                }
                return response('No authorization', 401);
            }
        }

        return $next($request);
    }
}
