<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs;
use App\Consultants;
use App\User;
use Carbon\Carbon;
use Auth;
use App\Submissions;

class ReportsController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function getHeadhunterUsers()
    {
        $jobs = User::whereIn('role', ['recruiter', 'adminhunters', 'head-hunters'])->whereIn('user_status', ['A'])->get();
        return response()->json(['users' => $jobs], 200);
    }
    public function getActiveJobReportsforAdmin(Request $request)
    {
        $jobs = [];
        if (\Auth::user()->getRoleNames()[0] == 'admin') {

            //->whereDate('created_at', Carbon::today())
            $value = $request->get('date');
            if ($value == '1') {
                $jobs = User::with(['Jobs' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }])->where('role', 'accountmanager')->get();
            } else if ($value == '2' || $value == '3' || $value == '4') {
                $jobs = User::with(['Jobs'])->where('role', 'accountmanager')->whereIn('user_status', ['A'])->get();
            } else {
                $jobs = User::with(['Jobs' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }])->where('role', 'accountmanager')->whereIn('user_status', ['A'])->get();
            }
        }
        return response()->json(['data' => $jobs], 200);
    }
    public function activeBenchTalents(Request $request)
    {
        $jobs = [];
        if (\Auth::user()->getRoleNames()[0] == 'admin') {

            //->whereDate('created_at', Carbon::today())

            $value = $request->get('date');
            if ($value == '1') {
                $jobs = User::withCount(['Consultants' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }, 'Consultants as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                    $q->where('consultant_status', '=', 'Placed');
                }])->whereIn('role', ['head-hunters', 'adminhunters'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '2') {
                $jobs = User::withCount(['Consultants' => function ($q) {
                    $q->whereDate('created_at', Carbon::yesterday());
                }, 'Consultants as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::yesterday());
                    $q->where('consultant_status', '=', 'Placed');
                }])->whereIn('role', ['head-hunters', 'adminhunters'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '3') {
                $jobs = User::withCount(['Consultants' => function ($q) {
                    $currentDate = \Carbon\Carbon::today()->subDays(7);
                    // $agoDate = $currentDate->subDays($currentDate->dayOfWeek)->subWeek();
                    $q->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')]);
                }, 'Consultants as placed' => function ($q) {

                    $currentDate = \Carbon\Carbon::today()->subDays(7);
                    // $agoDate = $currentDate->subDays($currentDate->dayOfWeek)->subWeek();
                    $q->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')]);
                    $q->where('consultant_status', '=', 'Placed');
                }])->whereIn('role', ['head-hunters', 'adminhunters'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '4') {
                $jobs = User::withCount(['Consultants' => function ($q) {
                    $q->whereDate('created_at', '>', Carbon::now()->subDays(30));
                }, 'Consultants as placed' => function ($q) {
                    $q->whereDate('created_at', '>', Carbon::now()->subDays(30));
                    $q->where('consultant_status', '=', 'Placed');
                }])->whereIn('role', ['head-hunters', 'adminhunters'])->whereIn('user_status', ['A'])->get();
            } else {
                $jobs = User::withCount(['Consultants' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }, 'Consultants as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                    $q->where('consultant_status', '=', 'Placed');
                }])->whereIn('role', ['head-hunters', 'adminhunters'])->whereIn('user_status', ['A'])->get();
            }
        }
        return response()->json(['data' => $jobs], 200);
    }
    public function activeBenchSales(Request $request)
    {
        $jobs = [];
        if (\Auth::user()->getRoleNames()[0] == 'admin') {

            //->whereDate('created_at', Carbon::today())

            $value = $request->get('date');
            if ($value == '1') {
                $jobs = User::withCount(['Submissions' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }, 'Submissions as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                    $q->where('submissionStatus', '=', 'Placed');
                }])->whereIn('role', ['bench-sales', 'jr-bench-sales'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '2') {
                $jobs = User::withCount(['Submissions' => function ($q) {
                    $q->whereDate('created_at', Carbon::yesterday());
                }, 'Submissions as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::yesterday());
                    $q->where('submissionStatus', '=', 'Placed');
                }])->whereIn('role', ['bench-sales', 'jr-bench-sales'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '3') {
                $jobs = User::withCount(['Submissions' => function ($q) {
                    $currentDate = \Carbon\Carbon::today()->subDays(7);
                    // $agoDate = $currentDate->subDays($currentDate->dayOfWeek)->subWeek();
                    $q->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')]);
                }, 'Submissions as placed' => function ($q) {

                    $currentDate = \Carbon\Carbon::today()->subDays(7);
                    // $agoDate = $currentDate->subDays($currentDate->dayOfWeek)->subWeek();
                    $q->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')]);
                    $q->where('submissionStatus', '=', 'Placed');
                }])->whereIn('role', ['bench-sales', 'jr-bench-sales'])->whereIn('user_status', ['A'])->get();
            } else if ($value == '4') {
                $jobs = User::withCount(['Submissions' => function ($q) {
                    $q->whereDate('created_at', '>', Carbon::now()->subDays(30));
                }, 'Submissions as placed' => function ($q) {
                    $q->whereDate('created_at', '>', Carbon::now()->subDays(30));
                    $q->where('submissionStatus', '=', 'Placed');
                }])->whereIn('role', ['bench-sales', 'jr-bench-sales'])->whereIn('user_status', ['A'])->get();
            } else {
                $jobs = User::withCount(['Submissions' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                }, 'Submissions as placed' => function ($q) {

                    $q->whereDate('created_at', Carbon::today());
                    $q->where('submissionStatus', '=', 'Placed');
                }])->whereIn('role', ['bench-sales', 'jr-bench-sales'])->whereIn('user_status', ['A'])->get();
            }
        }

        return response()->json(['data' => $jobs], 200);
    }
    public function jobreports(Request $request)
    {

        if (\Auth::user()->getRoleNames()[0] == 'admin') {
            $jobs = Jobs::with(['user', 'benchtalents', 'assignee' => function ($q) {

                $q->with(['users']);
            }])

                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->get();
        } else if (\Auth::user()->getRoleNames()[0] == 'adminhunters' || \Auth::user()->getRoleNames()[0] == 'head-hunters') {


            $jobs = Jobs::with(['user', 'technologies', 'clients', 'benchtalents' => function ($q) {
                $q->where('userId', '=', \Auth::user()->id);
            }, 'assignee', 'benchtalentswhere' => function ($q) {
                $q->where('consultant_status', '=', 'Interview Scheduled');
                $q->where('userId', '=', \Auth::user()->id);
            }, 'benchtalentswhereclient' => function ($q) {
                $q->where('consultant_status', '=', 'Submited Client');
                $q->where('userId', '=', \Auth::user()->id);
            }])
                ->whereHas('assignee', function ($query) {
                    $query->where('assign_id', '=', \Auth::user()->id);
                })

                ->orderBy('jobs.created_at', 'DESC')
                ->get();

        } else {
            $value = $request->get('value');
if( $value)
{
    $jobs = Jobs::withCount(['benchtalents','benchtalentswhere' => function ($q) use($request) {
        $q->where('consultant_status', '=', 'Interview Scheduled');
        $q->where('userId', '=',  $request->get('value'));
    }, 'benchtalentswhereclient' => function ($q) use($request) {
        $q->where('consultant_status', '=', 'Submited Client');
        $q->where('userId', '=',  $request->get('value'));
    },'benchtalentswhereclient as c2c' => function ($q) use($request) {
        $q->where('tax_type', '=', 'C2C');
        $q->where('userId', '=',  $request->get('value'));
    },'benchtalentswhereclient as w2' => function ($q) use($request) {
        $q->where('tax_type', '=', 'W2');
        $q->where('userId', '=',  $request->get('value'));
    },'benchtalentswhereclient as placed' => function ($q) use($request) {
        $q->where('consultant_status', '=', 'Placed');
        $q->where('userId', '=', $request->get('value'));

    }])
    ->whereHas('assignee', function ($query) use($request) {
        $query->where('assign_id', '=', $request->get('value'));
    })
        ->where('userId', '=', \Auth::user()->id)
        ->orderBy('jobs.created_at', 'DESC')
        ->get();
}else{
  /*  $jobs = Jobs::withCount(['benchtalents','benchtalentswhere' => function ($q) {
                    $q->where('consultant_status', '=', 'Interview Scheduled');

                }, 'benchtalentswhereclient' => function ($q) {
                    $q->where('consultant_status', '=', 'Submited Client');

                },'benchtalentswhereclient as c2c' => function ($q) {
                    $q->where('tax_type', '=', 'C2C');

                },'benchtalentswhereclient as w2' => function ($q) {
                    $q->where('tax_type', '=', 'W2');

                },'benchtalentswhereclient as placed' => function ($q) {
                    $q->where('consultant_status', '=', 'Placed');


                }])
        ->where('userId', '=', \Auth::user()->id)
        ->orderBy('jobs.created_at', 'DESC')
        ->get(); */
        $jobs = [];
}

        }





        return response()->json(['jobs' => $jobs], 200);
    }
    public function getApplicantReports()
    {
        if (\Auth::user()->getRoleNames()[0] == 'admin') {
            $aplicants =  Consultants::whereDate('created_at', Carbon::today())
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $aplicants =  Consultants::whereDate('created_at', Carbon::today())
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        return response()->json(['getaplicants' => $aplicants], 200);
    }
    public function getInterviewreports(Request $request)
    {
        if (\Auth::user()->getRoleNames()[0] == 'admin') {


            $value = $request->get('value');
            if ($value == '1') {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '2') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::tomorrow())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '3') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereBetween('schedule_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else if ($value == '4') {
                $currentDate = \Carbon\Carbon::today()->subDays(7);
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereBetween('schedule_date', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                    ->orderBy('created_at', 'DESC')
                    ->get();

            }else if ($value == '5') {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::yesterday())
                    ->orderBy('created_at', 'DESC')
                    ->get();

            }else if ($value == '6') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereMonth('schedule_date' , '=',Carbon::now()->month)
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();

                     }
                    return response()->json(['aplicants' => $jobs], 200);
        } else {
            $aplicants =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {


                $q->with(['clients']);
            }])
                ->where("consultant_status", '=', 'Interview Scheduled')
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
            return response()->json(['aplicants' => $aplicants], 200);
        }
    }

    public function getSubmitConsultants(Request $request)
    {

        $aplicants = [];
        if (\Auth::user()->getRoleNames()[0] == 'head-hunters' || \Auth::user()->getRoleNames()[0] == 'adminhunters') {

            //->whereDate('created_at', Carbon::today())

            $value = $request->get('value');
            if ($value == '1') {

                $aplicants =  Consultants::where("user_status", '=', 'p')
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->get();

            } else if ($value == '2') {

                $aplicants =  Consultants::where("user_status", '=', 'p')
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', Carbon::yesterday())
                ->orderBy('created_at', 'DESC')
                ->get();

            } else if ($value == '3') {
                $currentDate = \Carbon\Carbon::today()->subDays(7);
                $aplicants =  Consultants::where("user_status", '=', 'p')
                ->where('userId', '=', \Auth::user()->id)
                ->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                ->orderBy('created_at', 'DESC')
                ->get();



            } else if ($value == '4') {

                $aplicants =  Consultants::where("user_status", '=', 'p')
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', '>', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'DESC')
                ->get();


            } else {
                $aplicants =  Consultants::where("user_status", '=', 'p')
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->get();
            }
        }

        return response()->json(['aplicants' => $aplicants], 200);

    }
    public function getMyHotlistConsultants(Request $request)
    {
        $aplicants = [];
        $pendinghotlist = [];
        if (\Auth::user()->getRoleNames()[0] == 'head-hunters' || \Auth::user()->getRoleNames()[0] == 'adminhunters') {


            $aplicants =  Consultants::where("admin_status", '=', 'A')

                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
            $pendinghotlist =  Consultants::where("user_status", '=', 'p')
                ->where("admin_status", '=', 'D')
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        return response()->json(['data' => $aplicants, 'pendinghotlist' => $pendinghotlist], 200);
    }
    public function getJobsConsultants()
    {
        $aplicants = [];

        if (\Auth::user()->getRoleNames()[0] == 'admin' || \Auth::user()->getRoleNames()[0] == 'bdm'|| \Auth::user()->getRoleNames()[0] == 'accountmanager' ) {


            $aplicants =  Consultants::whereNotNull("job_id")
                    ->select([
                        '*',
                        \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                    ])
                ->orderBy('created_at', 'DESC')
                ->get();

        }

        return response()->json(['data' => $aplicants], 200);
    }
    public function getTargets()
    {
        if (\Auth::user()->getRoleNames()[0] == 'admin') {
            $getdailyCount = Consultants::whereIn('consultant_status', ['Interested'])
                ->whereDate('created_at', Carbon::today())
                ->get()->count();

            $getQualityCount = Consultants::whereIn('consultant_status', ['Interview Scheduled', 'Submited Client', 'Submited Prime Vendor'])

                ->whereDate('created_at', Carbon::today())
                ->get()->count();
            $activebench = Consultants::where('admin_status', '=', 'A')->get()->count();
            $submissions = Consultants::whereIn('consultant_status', ['Submited Client', 'Submited Prime Vendor'])
                ->get()->count();

            $myinterviews = Consultants::where('consultant_status', '=', 'Interview Scheduled')
                ->get()->count();
            $placed = Consultants::where('consultant_status', '=', 'Placed')
                ->get()->count();
        } else {
            $getdailyCount = Consultants::where('consultant_status', '=', 'Interested')
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', Carbon::today())
                ->get()->count();

            $getQualityCount = Consultants::whereIn('consultant_status', ['Interview Scheduled', 'Submited Client', 'Submited Prime Vendor'])
                ->where('userId', '=', \Auth::user()->id)
                ->whereDate('created_at', Carbon::today())
                ->get()->count();
            $activebench = Consultants::where('userId', '=', \Auth::user()->id)
                ->get()->count();
            $submissions = Consultants::whereIn('consultant_status', ['Submited Client', 'Submited Prime Vendor'])
                ->where('userId', '=', \Auth::user()->id)
                ->get()->count();

            $myinterviews = Consultants::where('consultant_status', '=', 'Interview Scheduled')
                ->where('userId', '=', \Auth::user()->id)
                ->get()->count();
            $placed = Consultants::where('consultant_status', '=', 'Placed')
                ->where('userId', '=', \Auth::user()->id)
                ->get()->count();
        }

        /* $vendorContactslist = [];
        $vendorContactslist[] =["label"=>"Choosse Consultant","value"=>""];
        foreach($contacts as $value)
        {
                $vendorContactslist[] =["label"=>$value->contactEmail,"value"=>$value->vendor_company_contact_id];

        } */
        return response()->json(['placed' => $placed, 'myinterviews' => $myinterviews, 'submissions' => $submissions, 'activebench' => $activebench, 'dailysubmissions' => $getdailyCount, 'qualitysubmissions' => $getQualityCount], 200);
    }

    public function getBenchtalentSubmissionReport()
    {
        $reports =  Consultants::with([
            //   'user', 'technologies',
            'user'
        ])->select([
            '*',
            \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
        ])
            ->withCount(['vendor_add' => function ($q) {


                $q->where('userId', '=', \Auth::user()->id);
                $q->where('submissionStatus', '=', 'Submitted to Client');
            }, 'vendor_cout' => function ($q) {


                $q->where('userId', '=', \Auth::user()->id);
                $q->where('submissionStatus', '=', 'Interview Scheduled');
            }, 'vendor_cout as totalsubmissions' => function ($q) {


                $q->where('userId', '=', \Auth::user()->id);
            },])
            ->orderBy('created_at', 'DESC')->get();
        return response()->json(['reports' => $reports], 200);
    }
    public function getTotalSubmissionsbyuser(Request $request)
    {

        $jobs = [];
        if (\Auth::user()->getRoleNames()[0] == 'bench-sales' ||\Auth::user()->getRoleNames()[0] == 'jr-bench-sales') {

            //->whereDate('created_at', Carbon::today())

            $value = $request->get('date');
            if ($value == '1') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else if ($value == '2') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->whereDate('created_at', Carbon::yesterday())
                    ->orderBy('created_at', 'DESC')
                    ->get();


            } else if ($value == '3') {
                $currentDate = \Carbon\Carbon::today()->subDays(7);

                    $jobs =   Submissions::with(['contactList' => function ($q) {
                            $q->with(['companies']);
                        }, 'clients', 'consultant' => function ($q) {

                            $q->select([
                                '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                            ]);
                        }])->where('userId', '=', \Auth::user()->id)
                        ->whereBetween('created_at', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                            ->orderBy('created_at', 'DESC')
                            ->get();


            } else if ($value == '4') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                     ->whereDate('created_at', '>', Carbon::now()->subDays(30))
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else {
                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();

            }
        }

        return response()->json(['data' => $jobs], 200);

/*
        $jobs = [];
        if (\Auth::user()->getRoleNames()[0] == 'bench-sales') {

            //->whereDate('created_at', Carbon::today())
            $value = $request->get('date');
            if ($value == '1') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)

                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '2' || $value == '3' || $value == '4') {
                $jobs =   Submissions::with(['contactList' => function ($q) {


                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)

                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else {

                $jobs =   Submissions::with(['contactList' => function ($q) {


                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->whereDate('created_at', Carbon::today())

                    ->orderBy('created_at', 'DESC')
                    ->get();
            }
        }
        return response()->json(['data' => $jobs], 200); */
    }
    public function getBenchsalesInterviewreports(Request $request)
    {
        $jobs = [];

        if (\Auth::user()->getRoleNames()[0] == 'bench-sales' || \Auth::user()->getRoleNames()[0] == 'jr-bench-sales') {


            $value = $request->get('date');
            if ($value == '1') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->where('submissionStatus', '=', 'Interview scheduled')
                    ->whereDate('scheduleDate', Carbon::today())
                    ->orderBy('scheduleDate', 'DESC')
                    ->get();

            } else if ($value == '2') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->where('submissionStatus', '=', 'Interview scheduled')
                    ->whereDate('scheduleDate', Carbon::tomorrow())
                    ->orderBy('scheduleDate', 'DESC')
                    ->get();


            } else if ($value == '3') {

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->where('submissionStatus', '=', 'Interview scheduled')
                    ->whereBetween('scheduleDate', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('scheduleDate', 'DESC')
                    ->get();


            } else if ($value == '4') {



                $currentDate = \Carbon\Carbon::today()->subDays(7);

                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->where('submissionStatus', '=', 'Interview scheduled')
                    ->whereBetween('scheduleDate', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                    ->orderBy('scheduleDate', 'DESC')
                    ->get();



            } else {
                $jobs =   Submissions::with(['contactList' => function ($q) {
                    $q->with(['companies']);
                }, 'clients', 'consultant' => function ($q) {

                    $q->select([
                        '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"),

                    ]);
                }])->where('userId', '=', \Auth::user()->id)
                    ->where('submissionStatus', '=', 'Interview scheduled')
                    ->whereDate('scheduleDate', Carbon::today())
                    ->orderBy('scheduleDate', 'DESC')
                    ->get();

                     }



        }
        return response()->json(['data' => $jobs], 200);
    }

    public function getInterviewsConsultants(Request $request)
    {

        if (\Auth::user()->getRoleNames()[0] == 'accountmanager' || \Auth::user()->getRoleNames()[0] == 'bdm') {


            $value = $request->get('value');
            if ($value == '1') {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '2') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::tomorrow())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '5') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::yesterday())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            }else if ($value == '6') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereMonth('schedule_date' , '=',Carbon::now()->month)
                    ->orderBy('created_at', 'DESC')
                    ->get();
            }else if ($value == '3') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereBetween('schedule_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else if ($value == '4') {
                $currentDate = \Carbon\Carbon::today()->subDays(7);
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereBetween('schedule_date', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();

                     }
                    return response()->json(['aplicants' => $jobs], 200);
        }  else if (\Auth::user()->getRoleNames()[0] == 'recruiter' ) {


            $value = $request->get('value');
            if ($value == '1') {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->where('userId', '=', \Auth::user()->id)
                    ->whereDate('schedule_date', Carbon::today())
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '2') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::tomorrow())
                    ->where('userId', '=', \Auth::user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else if ($value == '3') {

                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->where('userId', '=', \Auth::user()->id)
                    ->whereBetween('schedule_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('created_at', 'DESC')
                    ->get();

            } else if ($value == '4') {
                $currentDate = \Carbon\Carbon::today()->subDays(7);
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereBetween('schedule_date', [$currentDate->startOfWeek()->format('Y-m-d'), $currentDate->endOfWeek()->format('Y-m-d')])
                    ->orderBy('created_at', 'DESC')
                    ->where('userId', '=', \Auth::user()->id)
                    ->get();

            } else {
                $jobs =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with(
                        ['clients', 'user','primevendor']

                    );
                }])->has('withJobs')
                    ->where("consultant_status", '=', 'Interview Scheduled')
                    ->whereDate('schedule_date', Carbon::today())
                    ->where('userId', '=', \Auth::user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->get();

                     }
                    return response()->json(['aplicants' => $jobs], 200);
        } else {
            $aplicants =  Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {


                $q->with(['clients']);
            }])
                ->where("consultant_status", '=', 'Interview Scheduled')
                ->where('userId', '=', \Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->get();
            return response()->json(['aplicants' => $aplicants], 200);
        }
    }



}
