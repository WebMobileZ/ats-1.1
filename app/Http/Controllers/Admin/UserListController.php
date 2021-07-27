<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\UserNew;
use App\Model\UserEmployee;
use Auth;
use Mail;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Support\Facades\View;
use Swift_Transport;
use Swift_Message;

use Illuminate\Support\Facades\Hash;


class UserListController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\Auth::user()->getRoleNames()[0] == 'adminhunters') {
            $user = User::where('role', '=', 'adminhunters')->whereIn('user_status', ['A', 'B'])->get();
            return response()->json(['user' => $user], 200);
        } else if (\Auth::user()->getRoleNames()[0] == 'admin') {
            $where = [];
            // Request status fillter
            if ($request->get('role')) {
                if ($request->get('role') != "undefined")
                    $where['role'] = $request->role;
            }

            $user = User::where($where)->get();
            return response()->json(['user' => $user], 200);
        } else {
            $where = [];
            // Request status fillter
            if ($request->get('role')) {
                if ($request->get('role') != "undefined")
                    $where['role'] = $request->role;
            }

            $user = User::where($where)->get();
            return response()->json(['user' => $user], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = User::select('name', 'email', 'id','performance')->find(Auth::user()->id);

        return response()->json($user, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4'],
        ]);


        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => bcrypt($request->password),
            ]);

            $user->assignRole($request->role);
            if ($request->assigns) {

                foreach ($request->assigns as $value) {
                    $find = \App\UserAssign::where('userId', '=', $user->id)->where('assign_id', '=', $value)->get();
                    if (empty($find->count())) {
                        $createAssign = new \App\UserAssign();
                        $createAssign->userId = $user->id;
                        $createAssign->assign_id = $value;
                        $createAssign->save();
                    }
                }
            }
            //send token to the register user
            $token = $user->createToken('Laravel-Sanctum')->plainTextToken;

            return response()->json([
                'status_code' => 200,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'role' => $user->getRoleNames()
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = User::with(['user_assign_jr' => function ($q) {
            $q->with(['user']);
        }])->find($id);
        $assigness = \App\UserAssign::select('assign_id')->where('userId', '=', $id)->pluck('assign_id')->toArray();

        return response()->json(['user' => $user, 'assigns' => $assigness], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user = User::find($id);
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required'],
           // 'role' => ['required'],
        ]);
        $user = User::find($id);
        if ($request->name)
            $user->name      = $request->name;
        if ($request->email)
            $user->email     = $request->email;
        if ($request->user_status)
            $user->user_status     = $request->user_status;
            if ($request->performance)
            $user->performance     = $request->performance;
        if ($request->role) {
            if($request->role!=$user->role)
            {
                $user->removeRole($user->role);
                $user->assignRole($request->role);
                $user->role     = $request->role;
            }

        }

            $user->isResume = $request->isResume;

        if ($request->assigns) {
            $delete = \App\UserAssign::where('userId', $id)->delete();
            foreach ($request->assigns as $value) {
                $find = \App\UserAssign::where('userId', '=', $user->id)->where('assign_id', '=', $value)->get();
                if (empty($find->count())) {
                    $createAssign = new \App\UserAssign();
                    $createAssign->userId = $user->id;
                    $createAssign->assign_id = $value;
                    $createAssign->save();
                }
            }
        }

        if ($request->password)
            $user->password  = bcrypt($request->password);
        if ($request->out_look_pass)
            $user->out_look_pass  = $request->out_look_pass;
        $user->save();
        return response()->json(['user' => $user, 'data' => '', 'message' => 'User Updated Successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->user_status = 'D';
        $user->save();
        return response()->json(['user' => $user, 'message' => 'User Deleted Successfully'], 200);
    }
    public function getUserDetails()
    {
    }
}
