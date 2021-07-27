<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Submissions;
use App\Consultants;
use App\VendorList;
use App\Clients;
use App\User;
use Carbon\Carbon;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;

class SubmissionsController extends Controller
{

    public function emailsent(Request $request)
    {


/* start Up Email */
$consultant = '';
if( \Auth::user()->out_look_pass)
{
        if ($request->state) {

            if ($request->values) {

                $smtpAddress = 'smtp-mail.outlook.com';
                $port = 587;
                $encryption = 'tls';

                $yourEmail = \Auth::user()->email;
                if(\Auth::user()->out_look_pass)
                {
                    $yourPassword = \Auth::user()->out_look_pass;
                }else{
                    return response()->json(['sent' => 'Please Setup Outlook Email Password in My Profile', 'success' => false], 200);
                }

                $transport = (new \Swift_SmtpTransport($smtpAddress, $port, $encryption))
                    ->setUsername($yourEmail)
                    ->setPassword($yourPassword);
                $mailer = (new \Swift_Mailer($transport));


                $mail = (new \Swift_Message());
                $emailtosend = \Auth::user()->email;
                $ccemailsA = $request->values;
                $ccemails = implode(",", $ccemailsA);
                $html = $request->message;

                $mail->setFrom($emailtosend);
                $mail->setTo($ccemails);
                $mail->setSubject($request->subjectValue);
                $mail->setBody($html);
                $mail->setContentType('text/html');

                $file = '';
                $file2 = '';
                $file3 = '';
                $consultant = \App\Consultants::find($request->state);
                if ($consultant->resume) {
                    $file = storage_path('app/uploads/resume/' . $consultant->resume);
                    if ($request->resume)
                        $mail->attach(\Swift_Attachment::fromPath($file));
                }

                if ($consultant->otherDocument) {
                    $file2 = storage_path('app/uploads/otherDocument/' . $consultant->otherDocument);
                    if ($request->otherDocument)
                        $mail->attach(\Swift_Attachment::fromPath($file2));
                }

                if ($consultant->workAuthorization) {
                    $file3 = storage_path('app/uploads/workauthorization/' . $consultant->workAuthorization);
                    if ($request->workAuthorization)
                        $mail->attach(\Swift_Attachment::fromPath($file3));
                }


                if ($mailer->send($mail)) {
                    return response()->json(['sent' => "Email Send Successfully You can Check Sent Emails in OutLook", 'success' => true], 200);
                } else {
                    return response()->json(['sent' => 'Email Sent Error please Check Password Or Try Again', 'success' => false], 200);
                }
            }else{
                return response()->json(['sent' => 'Please Enter To Email Address', 'success' => false], 200);
            }


        } else {
            return response()->json(['sent' => 'Please Select Candidate', 'success' => false], 200);

        }
    }else{
        return response()->json(['sent' => 'Please Setup Outlook Email Password in My Profile', 'success' => false], 200);
    }

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getConsultansOnly()
    {
        $consultantlist = Consultants::orderBy('created_at', 'DESC')
            //->where('adminStatus', '=', 'A')
            ->get();
        $alllist = [];

        foreach ($consultantlist as $value) {
            $alllist[] = ["label" => $value->first_name . " " . $value->last_name, "value" => $value->consultant_id];
        }
        return response()->json(['consultants' => $alllist], 200);
    }

    public function getConsultans()
    {

        $submissions = \App\Consultants::orderBy('created_at', 'DESC')
            ->where('admin_status', '=', 'A')
            ->get();
        $submissionslist = [];
        $submissionslist[] = ["label" => "Choosse Consultant", "value" => ""];
        foreach ($submissions as $value) {
            $submissionslist[] = ["label" => $value->first_name . " " . $value->last_name, "value" => $value->consultant_id];
        }
        $vendors = \App\Companies::orderBy('created_at', 'DESC')
            ->get();
        $vendorslist = [];
        $vendorslist[] = ["label" => "Choosse Vendor", "value" => ""];
        foreach ($vendors as $value) {
            $vendorslist[] = ["label" => $value->name, "value" => $value->vendor_company_id];
        }
        $clients = \App\Clients::orderBy('created_at', 'DESC')
            ->get();
        $clientList = [];
        $clientList[] = ["label" => "Choosse Client", "value" => ""];
        foreach ($clients as $value) {
            $clientList[] = ["label" => $value->name, "value" => $value->client_id];
        }

        $jobList = [];

        $data = array();
        /* $data = Submissions::with('user_details','consultant')
                ->whereDate('created_at', Carbon::today())
                ->where("userId", "=", \Auth::user()->id)
                ->orderBy('created_at', 'DESC')->get(); */

        return response()->json(['submissions' => $submissionslist, 'vendorslist' => $vendorslist, "clients" => $clientList, "jobs" => $jobList, 'data' => $data], 200);
    }

    public function getTotalInterviewShecdules(Request $request)
    {
        $interViewSubmissions = Submissions::find($request->vid)
            ->where('submissionStatus', '=', 'Interview scheduled')

            ->get();
        $interViewCount = $interViewSubmissions->count();
        $submitclientSubmissions = Submissions::find($request->vid)
            ->where('submissionStatus', '=', 'Submitted to Client')

            ->get();
        $submitclientCount = $submitclientSubmissions->count();
        return response()->json(['interviews' => $interViewCount, 'submitclient' => $submitclientCount], 200);
    }
    public function  getMySubmissions(Request $request)
    {
        $where = [];


        if ($request->get('consultant_id')) {
            $where['consultant_id'] = $request->consultant_id;
        }
        if ($request->get('submissionRate')) {
            $where['submissionRate'] = $request->submissionRate;
        }
        if ($request->get('actualRate')) {
            $where['actualRate'] = $request->actualRate;
        }
        /*   if ($request->get('technology')) {

            $where['technology'] =$request->technology;
        } */
        //    if (Auth::user()->role != "Admin")
        $where['userId'] = \Auth::user()->id;


        $submissions = Submissions::with(['user_details', 'consultant' => function ($q) {

            $q->select([
                '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as consultatName")

            ]);
        }])
            ->whereHas('consultant', function ($q) use ($request) {
                if ($request->get('technology'))
                    $q->where('technology', 'like', '%' . $request->get('technology') . '%');
            })
            ->where($where)

            ->when($request->get('vendorCompanyName'), function ($query) use ($request) {
                $query->where('vendorCompanyName', 'like', '%' . $request->get('vendorCompanyName') . '%');
            })
            ->when($request->get('vendorName'), function ($query) use ($request) {
                $query->where('vendorName', 'like', '%' . $request->get('vendorName') . '%');
            })

            ->when($request->get('vendorEmail'), function ($query) use ($request) {
                $query->where('vendorEmail', 'like', '%' . $request->get('vendorEmail') . '%');
            })
            ->when($request->get('vendorMobileNumber'), function ($query) use ($request) {
                $query->where('vendorMobileNumber', 'like', '%' . $request->get('vendorMobileNumber') . '%');
            })
            ->when($request->get('endClientName'), function ($query) use ($request) {
                $query->where('endClientName', 'like', '%' . $request->get('endClientName') . '%');
            })

            ->when($request->get('submissionStatus'), function ($query) use ($request) {

                $arrha = explode(',', $request->get('submissionStatus'));
                $query->whereIn('submissionStatus', $arrha);
            })
            ->when($request->get('created_at'), function ($query) use ($request) {
                $query->whereDate('created_at', '=', \Carbon\Carbon::parse($request->get('created_at'))->format('Y-m-d'));
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        // $submissions = Submissions::with('user_details','consultant','vendorlist','clients','vendorDetail')->orderBy('created_at', 'DESC')


        return response()->json(['submissions' => $submissions], 200);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // filters
        $where = [];
        $whereIn=[];
        $whereBetween=0;
        $assigness=[];
       /* $submissions = Submissions::get();
        $i=0;
        foreach(  $submissions as $value)
        {
            if(empty($value->vendor_company_contact_id))
            {


                $contactid = \App\Contacts::where('contactEmail','like',$value->vendorEmail)->get();

                if($contactid->count())
                {
                if($contactid[0]->vendor_company_contact_id)
                    {
$i++;                   $submission = \App\Submissions::find($value->submission_id );
                        $submission->vendor_company_contact_id  =$contactid[0]->vendor_company_contact_id;
                        $submission->save();
                    }
                }



           }
        }

         */



        if ($request->get('consultantid')) {
            if ($request->get('consultantid') != "undefined")
                $where['consultant_id'] = $request->consultantid;
        }
        if ($request->get('status')) {
            if ($request->get('status') != "undefined")
                $where['submissionStatus'] = $request->status;
        }

        //    if (Auth::user()->role != "Admin")
        // $where['userId'] = \Auth::user()->id;

        if ((\Auth::user()->getRoleNames()[0] != 'admin') ) {
            if (\Auth::user()->getRoleNames()[0] == 'sales-lead') {
                $whereIn=[];
            if ($request->get('userId')) {
                if ($request->get('userId') != "undefined")
                    $where['userId'] = $request->userId;
            }
           }

            if (\Auth::user()->getRoleNames()[0] == 'jr-bench-sales') {
                 $whereIn=[\Auth::user()->id];
            }
            if (\Auth::user()->getRoleNames()[0] == 'bench-sales') {

                $assigness= \App\UserAssign::select('assign_id')->where('userId','=',\Auth::user()->id)->pluck('assign_id')->toArray();

               $whereIn =  $assigness;
               $whereIn[]=\Auth::user()->id;
                }
                $whereBetween =1;

        }else{
            $whereIn=[];
            if ($request->get('userId')) {
                if ($request->get('userId') != "undefined")
                    $where['userId'] = $request->userId;
            }
        }


        $submissions = Submissions::with(['contactList' => function ($q) {


            $q->with(['companies']);
        }, 'clients', 'user_details', 'consultant' => function ($q) {

            $q->select([
                '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as consultatName"),

            ]);
            $q->with(['technologies']);
        }])
       ->whereHas('consultant', function ($q) use ($request) {
                if ($request->get('technology'))
                    $q->where('technology', 'like', '%' . $request->get('technology') . '%');
            })
            ->where($where)
        //      ->whereIn('userId', $whereIn)
            ->when($whereIn, function ($query, $whereIn) {
                if( $whereIn)
                return $query->whereIn('userId', $whereIn);
            })
            ->when($whereBetween, function ($query, $whereBetween) {
                if( $whereBetween)
                return $query->whereBetween('created_at', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear(),
                ]);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);


        // $submissions = Submissions::with('user_details','consultant','vendorlist','clients','vendorDetail')->orderBy('created_at', 'DESC')


        return response()->json(['submissions' => $submissions, 'status' => '','assoin'=> $assigness], 200);
    }
    public function interviewsubmissions()
    {
        // $submissions = Submissions::with('user_details','consultant','vendorlist','clients','vendorDetail')
        $submissions = Submissions::with(['user_details', 'consultant' => function ($q) {

            $q->select([
                '*',
                \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as consultatName"), \DB::raw("CASE technology
                WHEN 'others' THEN otherTechnologies ELSE technology END AS technology")
            ]);
        }])
            ->whereIn('submissionStatus', ['Interview scheduled', 'Placed'])
            ->orderBy('scheduleDate', 'DESC')
            ->get();

        return response()->json(['submissions' => $submissions], 200);
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

            'vendorDetailId' => 'required',
            'clientId' => Rule::unique('submissions')->where(function ($query) use ($request) {
                return $query->where('vendor_company_contact_id', $request->vendor_company_contact_id)
                    ->where('consultant_id', $request->state)
                    ->where('clientId', $request->clientId);
            })
        ]);


        $consultant = '';
        /* Email Function New
        if ($request->state) {
            $consultant = \App\Consultants::with('technologies')->find($request->state);
            $emailid =  \App\Contacts::with('companies')->find($request->vendorDetailId);
            $smtpAddress = 'mail.webmobilez.com';
            $port = 26;
            $encryption = 'tls';

            $yourEmail = 'info@webmobilez.com';
            $yourPassword = 'Webmobilez$543';

            $transport = (new \Swift_SmtpTransport($smtpAddress, $port, $encryption))
                ->setUsername($yourEmail)
                ->setPassword($yourPassword);
            $mailer = (new \Swift_Mailer($transport));
            $file = '';
            $file2 = '';
            $file3 = '';
            if ($consultant->resume)
                $file = storage_path('app/uploads/resume/' . $consultant->resume);
            if ($consultant->otherDocument)
                $file2 = storage_path('app/uploads/otherDocument/' . $consultant->otherDocument);
            if ($consultant->workAuthorization)
                $file3 = storage_path('app/uploads/workauthorization/' . $consultant->workAuthorization);

            $mail = (new \Swift_Message());
            $emailtosend = \Auth::user()->email;
            //  $ccemailsA = $request->cc;
            //  $ccemails = implode(",", $ccemailsA);
            $getUser = User::find(\Auth::user()->id);

            $view = View::make('email_template', [
                'message' => $getUser, 'company' => $emailid
            ]);

            $html = $view->render();
            $subject = "BenchInfo - " . $consultant->technologies->name . " " . $consultant->state . " " . $consultant->city;
            if ($emailid->contactEmail) {
                $mail->setFrom($yourEmail);
                $mail->setTo($emailid->contactEmail);
                $mail->setSubject($subject);
                $mail->setBody($html);
                $mail->setContentType('text/html');
                if ($consultant->resume)
                    $mail->attach(\Swift_Attachment::fromPath($file));
                if ($consultant->otherDocument)
                    $mail->attach(\Swift_Attachment::fromPath($file2));
                if ($consultant->workAuthorization)
                    $mail->attach(\Swift_Attachment::fromPath($file3));
                $mailer->send($mail);
            }
        }
        */

        $job = new \App\Submissions();
        $job->actualRate = $request->actualRate;
        $job->comments = $request->vendorComments;
        $job->submissionRate = $request->submissionRate;
        $job->vendor_company_contact_id = $request->vendorDetailId;
        $job->clientId = $request->clientId;
        $job->consultant_id = $request->state;
        $job->submissionStatus = 'Submitted to Vendor';
        $job->endClientLocation = $request->endClientLocation;
      /* if( $request->vendorStatus == 'Interview scheduled' )
        {
            if ($request->timezone)
            $job->timezone = $request->timezone;
        if ($request->scheduleDate)
            $job->scheduleDate =  Carbon::parse($request->scheduleDate);
        } */




        $job->userId = \Auth::user()->id;
        $job->created_at = date('Y-m-d H:i:s');
        $job->save();



        $data = Submissions::with(['user_details', 'clients', 'contactList' => function ($q) {


            $q->with(['companies']);
        }, 'consultant' => function ($q) {

            $q->select([
                '*',
                \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as name"), \DB::raw("CASE technology
                WHEN 'others' THEN otherTechnologies ELSE technology END AS technology")
            ]);
        }])
            ->where('submission_id', '=', $job->submission_id)
            ->get();

        return response()->json(['job' => $job, 'data' => $data], 200);
    }
    public function statusChange(Request $request)
    {
        $rules = [
            'index' => 'required|numeric',
            //   'status' => 'required|in:p,u',
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            \Log::error($validator);
            return array('error' => true, 'msg' => 'Some thing went wrong');
        } else {
            if (Auth::user()->role == "Admin") {
                $user = \App\Reports::find($request->index);
                $user->adminStatus = 'A';
                $user->save();
                $timesheet =  \App\Reports::with('user_details')
                    ->where("userStatus", '=', 'p')
                    ->where('reports.wStatus', '!=', 'R')
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else {
                $user = \App\Reports::find($request->index);
                $user->userStatus = 'p';
                $user->save();
                $timesheet =  \App\Reports::with('user_details')
                    ->where("userId", "=", \Auth::user()->id)
                    ->where('reports.wStatus', '!=', 'R')
                    ->orderBy('created_at', 'DESC')
                    ->get();
            }



            return response()->json(['timesheet' => $timesheet], 200);
        }
    }
    public function show($id)
    {
        // $submissions = Submissions::with('user_details','vendorlist','clients','vendorDetail')
        $submissions =
            Submissions::with(['contactList' => function ($q) {


                $q->with(['companies']);
            }, 'clients', 'user_details'])
            ->where('consultant_id', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json(['submissions' => $submissions], 200);
    }
    public function edit($id)
    {
        $submissions = Submissions::with('user_details')
            ->where('consultant_id', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json(['submissions' => $submissions], 200);
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
            'submissionStatus' => 'required',
        ]);
        $submission = \App\Submissions::find($id);
        $submission->submissionStatus = $request->submissionStatus;
        if ($request->scheduleDate)
            $submission->scheduleDate = $request->scheduleDate;
        if ($request->timezone)
            $submission->timezone = $request->timezone;
        $submission->comments = $request->comments;
        $submission->save();

        $submission = Submissions::with(['contactList' => function ($q) {


            $q->with(['companies']);
        }, 'clients', 'user_details', 'consultant' => function ($q) {

            $q->select([
                '*', \DB::raw("CONCAT(COALESCE(first_name	, ''),' ',COALESCE(last_name, '')) as consultatName"),

            ]);
            $q->with(['technologies']);
        }])
            ->whereHas('consultant', function ($q) use ($request) {
                if ($request->get('technology'))
                    $q->where('technology', 'like', '%' . $request->get('technology') . '%');
            })
            ->find($submission->submission_id);

        return response()->json(['submission' => $submission], 200);
    }
    public function getUsers()
        {

            $users = \App\User::orderBy('created_at', 'DESC')
           ->whereIn('role', array('bench-sales','jr-bench-sales','sales-lead'))
            ->get();

            return response()->json(['users' => $users ], 200);

        }
}

