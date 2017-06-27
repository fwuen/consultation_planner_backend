<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UserController;
use Closure;

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
        $users = \DB::table('users')->where('id', $user_id)->get();
        $email = null;
        if ($users->isEmpty()) {
            return response('No authorization', 401);
        }
        foreach ($users as $user) {
            $last_refresh = strtotime($user->updated_at);
            $current_time = time();
            $difference = $current_time - $last_refresh;
            if ($user->token == $value) {
                if ($difference < 3600) {
                    \DB::table('users')->where('id', $user_id)->update(['updated_at' => date('Y-m-d G:i:s')]);
                }
            } else {
                return response('No authorization', 401);
            }
            $email = $user->email;
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
