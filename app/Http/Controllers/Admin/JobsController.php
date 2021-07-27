<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs;
use Carbon\Carbon;
use Auth;

class JobsController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (\Auth::user()->getRoleNames()[0] == 'admin') {

            if ($request->userId || $request->consultant_id || $request->status) {

                if (($request->consultant_id === 'null') && ($request->consultant_id === 'null')) {

                    if($request->status)
                    {
                        $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) use ($request) {
                            $q->with('user');
                        }])

                            ->where("status", '=', $request->status)
                            // ->where('userId','=',\Auth::user()->id)
                            ->orderBy('created_at', 'DESC')
                            ->get();
                    }
                }else {

                $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) use ($request) {
                    $q->with('user');
                    if ($request->userId) {
                        if ($request->userId === 'null') {
                        } else {
                            $q->where('userId', '=', $request->userId);
                        }
                    }
                    if ($request->consultant_id) {
                        if ($request->consultant_id === 'null') {
                        } else {
                            $q->where('consultant_id', '=', $request->consultant_id);
                        }
                    }
                }])
                    ->whereHas('benchtalents', function ($q) use ($request) {

                        if ($request->userId) {
                            if ($request->userId === 'null') {
                            } else {
                                $q->where('userId', '=', $request->userId);
                            }
                        }
                        if ($request->consultant_id) {
                            if ($request->consultant_id === 'null') {
                            } else {
                                $q->where('consultant_id', '=', $request->consultant_id);
                            }
                        }
                    })
                     ->where("status", '=', $request->status)
                    // ->where('userId','=',\Auth::user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->get();
                }
            } else {
                $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) use ($request) {
                    $q->with('user');
                }])

                    ->where("status", '=', 'Active')
                    // ->where('userId','=',\Auth::user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->get();
            }
        } else if (\Auth::user()->getRoleNames()[0] == 'accountmanager' || \Auth::user()->getRoleNames()[0] == 'a-b-manager' || \Auth::user()->getRoleNames()[0] == 'bdm') {

            $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents'])
                ->where("status", '=', 'Active')
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
        } else if (\Auth::user()->getRoleNames()[0] == 'recruiter') {
            $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
                $q->where('userId', '=', \Auth::user()->id);
            }, 'assignee'])
                ->whereHas('assignee', function ($query) {
                    $query->where('assign_id', '=', \Auth::user()->id);
                })
                ->where("status", '=', 'Active')
                ->orderBy('jobs.created_at', 'DESC')
                ->get();
        } else if (\Auth::user()->getRoleNames()[0] == 'adminhunters' || \Auth::user()->getRoleNames()[0] == 'head-hunters') {
            $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
                $q->where('userId', '=', \Auth::user()->id);
            }, 'assignee'])
                ->whereHas('assignee', function ($query) {
                    $query->where('assign_id', '=', \Auth::user()->id);
                })
                ->where("status", '=', 'Active')
                ->orderBy('jobs.created_at', 'DESC')
                ->get();
        }
        return response()->json(['jobs' => $jobs], 200);
    }


    /**$journal
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'job_title' => 'required',

        ]);

        $job = new \App\Jobs();
        $job->job_code = "1233";
        $job->job_title = $request->job_title;
        $job->technology_id  = $request->technology_id;
        if ($request->client_id)
            $job->client_id = $request->client_id;
        $job->city  = $request->city;
        $job->state = $request->state;
        $job->client_bill_rate = $request->client_bill_rate;
        $job->pay_rate = $request->pay_rate;
        $job->experience = $request->experience;
        $job->duration = 0;
        $job->visa_type = serialize($request->visa_type);
        $job->work_type = $request->work_type;
        //  $job->main_requirement = $request->main_requirement;

        if ($request->mandatoryskills) {
            $job->mandatoryskills = serialize($request->mandatoryskills);
        }
        if ($request->vendor_company_id) {
            $job->vendor_company_id = $request->vendor_company_id;
        }
        if ($request->vendorDetailId) {
            $job->vendorDetailId = $request->vendorDetailId;
        }
        $job->description = $request->description;
        //  $job->responsibilities = $request->responsibilities;
        if ($request->status) {
            $job->status = $request->status;;
        }
        if ($request->w2_rate) {
            $job->w2_rate = $request->w2_rate;;
        }
        $job->userId = \Auth::user()->id;
        $job->created_at = date('Y-m-d H:i:s');
        $job->save();
        if ($request->assign_id) {
            $delete = \App\JobAssign::where('job_id', $job->job_id)->delete();
            foreach ($request->assign_id as $value) {
                $find = \App\JobAssign::where('job_id', '=', $job->job_id)->where('assign_id', '=', $value)->get();
                if (empty($find->count())) {
                    $createAssign = new \App\JobAssign();
                    $createAssign->job_id = $job->job_id;
                    $createAssign->assign_id = $value;
                    $createAssign->save();
                }
            }
        }


        $job = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
            $q->with('user');
        }])->find($job->job_id);

        return response()->json(['job' => $job], 200);
    }

    public function show($id)
    {
        //
        if (\Auth::user()->getRoleNames()[0] == 'admin' || \Auth::user()->getRoleNames()[0] == 'accountmanager' || \Auth::user()->getRoleNames()[0] == 'a-b-manager' || \Auth::user()->getRoleNames()[0] == 'bdm') {
            $job = \App\Jobs::with(['user', 'technologies', 'clients', 'benchtalents', 'assignee' => function ($q) {

                $q->with(['users']);
            }])->find($id);
        } else {
            $job = \App\Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
                $q->where('userId', '=', \Auth::user()->id);;
            }, 'assignee' => function ($q) {

                $q->with(['users']);
            }])->find($id);
        }

        return response()->json(['job' => $job], 200);
    }
    public function edit($id)
    {

        /* $job = \App\Jobs::with(['user','technologies','clients','assignee'])->find($id);
        return response()->json(['job' => $job], 200); */
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



        $this->validate($request, [
            'job_title' => 'required',
        ]);

        $job = \App\Jobs::find($id);
        $job->job_code = "1233";
        $job->job_title = $request->job_title;
        if ($request->technology_id)
            $job->technology_id  = $request->technology_id;
        if ($request->client_id)
            $job->client_id = $request->client_id;
        if ($request->partner_id)
            $job->partner_id = $request->partner_id;
        $job->city  = $request->city;
        $job->state = $request->state;
        $job->client_bill_rate = $request->client_bill_rate;
        $job->pay_rate = $request->pay_rate;
        $job->experience = $request->experience;
        $job->duration = 0;
        $job->visa_type = serialize($request->visa_type);
        $job->work_type = $request->work_type;

        if ($request->mandatoryskills) {
            $job->mandatoryskills = serialize($request->mandatoryskills);
        }
        if ($request->vendor_company_id) {
            $job->vendor_company_id = $request->vendor_company_id;
        }
        if ($request->vendorDetailId) {
            $job->vendorDetailId = $request->vendorDetailId;
        }
        if ($request->w2_rate) {
            $job->w2_rate = $request->w2_rate;;
        }
        // $job->main_requirement = $request->main_requirement;
        $job->description = $request->description;
        $job->responsibilities = $request->responsibilities;
        if ($request->status) {
            $job->status = $request->status;;
        }
        $job->save();

        //  $values =json_decode($request->assign_id, true);

        if ($request->assign_id) {
            $delete = \App\JobAssign::where('job_id', $job->job_id)->delete();
            foreach ($request->assign_id as $value) {
                $find = \App\JobAssign::where('job_id', '=', $job->job_id)->where('assign_id', '=', $value)->get();
                if (empty($find->count())) {
                    $createAssign = new \App\JobAssign();
                    $createAssign->job_id = $job->job_id;
                    $createAssign->assign_id = $value;
                    $createAssign->save();
                }
            }
        } else {
            $delete = \App\JobAssign::where('job_id', $job->job_id)->delete();
        }

        $job = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
            $q->with('user');
        }])->find($job->job_id);
        return response()->json(['job' => $job], 200);
    }
    public function getUsers()
    {

        $consultantlist = \App\User::orderBy('created_at', 'DESC')
            ->where('user_status', '=', 'A')
            ->get();
        $alllist = [];

        foreach ($consultantlist as $value) {
          //  $user = \App\User::find($value->id);

          //  $alllist[] = ["label" => $value->name . '-' . $user->getRoleNames(), "value" => $value->id];
          $alllist[] = ["label" => $value->name, "value" => $value->id];
        }

        return response()->json(['users' => $alllist], 200);
    }
    public function getUserAssignJobs()
    {
        if (\Auth::user()->getRoleNames()[0] == 'admin') {
            $jobs =  \DB::table('jobs')
                //  ->join('job_assigns', 'jobs.job_id', '=', 'job_assigns.job_id')
                ->select('jobs.job_id', 'jobs.job_title')
                ->where("jobs.status", '=', 'Active')
                ->get();
        } else {
            $jobs =  \DB::table('jobs')
                ->join('job_assigns', 'jobs.job_id', '=', 'job_assigns.job_id')
                ->select('jobs.job_id', 'jobs.job_title')
                ->where("jobs.status", '=', 'Active')
                ->where('job_assigns.assign_id', '=', \Auth::user()->id)
                ->get();
        }


        return response()->json(['jobs' =>  $jobs], 200);
    }
}
