<?php

namespace App\Http\Controllers\Admin;

use App\Consultants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Profile;
use Mockery\Undefined;
use Carbon\Carbon;
use File;
use App\Http\Controllers\Admin\DocxConversionController;
use App\Technologies;

class ConsultantsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function documentsconsultants()
    {


        return response()->json([

            'consultants' => Consultants::with([
                'user', 'technologies'
                //'submissions' => function ($q) {


                //  $q->with(['user_details','clients','contactList'=> function ($q) {


                //  $q->with(['companies']);
                // }
                //  ]);
                // }
            ])->select([
                '*',
                \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
            ])->withCount([
                'vendor_cout as sclients' => function ($query) {
                    $query->where('submissions.submissionStatus', '=', 'Submitted to Client');
                },
                'vendor_cout as interviews' => function ($query) {
                    $query->where('submissions.submissionStatus', '=', 'Interview scheduled');
                }, 'vendor_cout as submissionscount'
            ])
                ->where('admin_status', '=', 'A')
                ->where('consultant_type', '=', 'A')
                ->orderBy('created_at', 'DESC')->get()
        ]);
    }
    public function index(Request $request)
    {
        $where = [];
        if (\Auth::user()->getRoleNames()[0] == 'admin' || \Auth::user()->getRoleNames()[0] == 'adminhunters') {



           if ($request->mysubmissions == 2){
                $where['userId'] = \Auth::user()->id;
            }

            if ($request->status) {
                if ($request->status != 'undefined')
                    $where['consultant_status'] = $request->status;
            }
            if ($request->userId) {
                $where['userId'] = $request->userId;
            }
            if ($request->technology_id ) {
                $where['technology_id'] = $request->technology_id;
            }

                if (\Auth::user()->getRoleNames()[0] == 'admin' )
                {
                    if ($request->admin_status ) {
                        $where['admin_status'] = $request->admin_status;
                    }else{
                    $where['admin_status'] = 'A';
                      }
                }


            // $where['admin_status'] = 'D';

            /*
            if ($request->hotlist=='Only Hotlist')
            {
                $where['admin_status'] = 'A';
            }
            if ($request->hotlist=='Exclude Hotlist')
            {
                $where['admin_status'] = 'D';
            } */
            // filters


            return response()->json([


                'consultants' => Consultants::with([
                    'user', 'technologies', 'withJobs' => function ($q) {
                        $q->with('user');
                    }
                    //'submissions' => function ($q) {


                    //  $q->with(['user_details','clients','contactList'=> function ($q) {


                    //  $q->with(['companies']);
                    // }
                    //  ]);
                    // }
                ])->select([
                    '*',
                    \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                ])
                    ->where($where)
                    ->orderBy('created_at', 'DESC')
                    ->where('user_status', '=', 'p')
                    ->where('consultant_type', '=', 'A')
                    ->get()
            ]);
        } else {
            return response()->json([

                'consultants' => Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with('user');
                }, 'submissions' => function ($q) {


                    $q->with(['user_details', 'clients', 'contactList' => function ($q) {


                        $q->with(['companies']);
                    }]);
                }])->select([
                    '*',
                    \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                ])
                    ->where('userId', '=', \Auth::user()->id)
                    ->where('user_status', '=', 'u')
                    ->where('consultant_type', '=', 'A')
                    //  ->where('admin_status', '=', 'D')
                    ->orderBy('created_at', 'DESC')->get()
            ]);
        }
    }
    public function getDocumentConsutants()
    {
        //
        return response()->json([
            'data' => 'Logged in as:- ',
            'profile' => Auth::user()->profile,
            'consultants' => Consultants::with(['user', 'technologies', 'submissions'])->select([
                '*',
                \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
            ])->where('consultant_type', '=', 'A')->get()
        ]);
    }
    public function getHotList(Request $request)
    {
        /*  $hotlist = \App\Reports::select("*", \DB::raw("CASE technology
        WHEN 'others' THEN otherTechnologies ELSE technology END AS technology"))
            ->where('reports.wStatus', '=', 'A')
            ->where('reports.adminStatus', '=', 'A')
            ->orderBy('reports.created_at', 'desc')
            ->get();


        return response()->json(['hotlist' => $hotlist], 200); */

        $where = [];
        // Request status fillter
        if ($request->param) {

            $hotlist = Consultants::where('user_status', '=', 'p')
                ->get();
            $ids = [];

            foreach ($hotlist as $value) {
                if ($value->resume) {
                    $filename = $value->resume;
                    $id = $value->consultant_id;
                    $path = storage_path('app/uploads/resume/' .  $filename);


                    //  if(!File::exists($path)) {

                    $striped_content = '';
                    $content = '';
                    $striped_content = new DocxConversionController($path);
                    $value = false;
                    $params = explode(",", $request->param);
                    foreach ($params as  $value) {
                        if (stripos($striped_content->convertToText(), $value) !== false) {

                            $ids[] = $id;
                        }
                    }
                    //  }
                }
            }
            if (count($ids) >= 1) {
                $hotlist = Consultants::with(['user', 'technologies'])
                    //->orWhereHas('technologies', function($q) use($request){

                    //   $q->orWhere('name', 'like', '%' . $request->param . '%');

                    //   })
                    ->whereIn('consultant_id', $ids)->get();
            } else {
                $hotlist = Consultants::with(['user', 'technologies'])
                    ->whereHas('technologies', function ($q) use ($request) {
                        $params = explode(",", $request->param);
                        foreach ($params as  $value) {
                            $q->orWhere('name', 'like', '%' . $value . '%');
                        }
                    })
                    ->get();
            }

            return response()->json([
                'hotlist' => $hotlist, 'ids' => $ids

            ]);
        } else {
            $hotlist = Consultants::with(['user', 'technologies'])->where('admin_status', '=', 'A')->get();
            return response()->json([
                'hotlist' => $hotlist,

            ]);
        }
    }

    public function getHotListOnly(Request $request)
    {

        $hotlist = Consultants::with(['user', 'technologies'])->where('admin_status', '=', 'A') ->where('consultant_type', '=', 'A')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'hotlist' => $hotlist

        ]);
    }
    public function getHotListKeyword(Request $request)
    {
        /*  $hotlist = \App\Reports::select("*", \DB::raw("CASE technology
        WHEN 'others' THEN otherTechnologies ELSE technology END AS technology"))
            ->where('reports.wStatus', '=', 'A')
            ->where('reports.adminStatus', '=', 'A')
            ->orderBy('reports.created_at', 'desc')
            ->get();


        return response()->json(['hotlist' => $hotlist], 200); */
        $hotlist = [];
        $where = [];
        if( \Auth::user()->isResume)
        {
        // Request status fillter
        if ($request->param || $request->technology || $request->visaType || $request->experience || $request->tax_type) {

            //  $hotlist = Consultants::where('user_status', '=', 'p')
            //  ->get();
            //  $ids =[];
            /* Resume search
           foreach($hotlist as $value)
            {
               if($value->resume)
               {
                $filename = $value->resume;
                $id = $value->consultant_id;
                $path = storage_path('app/uploads/resume/' .  $filename );


              //  if(!File::exists($path)) {

                            $striped_content = '';
                            $content = '';
                            $striped_content = new DocxConversionController($path);
                            $value = false;
                            $params= explode(",",$request->param);
                            foreach ($params as  $value)
                            {
                                if (stripos($striped_content->convertToText(),$value) !== false) {

                                    $ids[]=$id;
                                }
                             }
                //  }
               }
             } */
            //   if(count($ids)>=1)
            //   {
            $hotlist = Consultants::with(['user', 'technologies'])
                //  ->whereHas('technologies', function($q) use($request){
                ///    $params= explode(",",$request->param);
                //       foreach ($params as  $value)
                //     {
                //    $q->orWhere('name', 'like', '%' . $value . '%');
                //   }
                //  })
                ->whereNested(function ($q) use ($request) {
                    if ($request->param) {
                        $params = explode(",", $request->param);
                        foreach ($params as  $value) {
                            $string =  preg_replace('/\s+/', '', $value);
                            $q->where('keywords', 'like', '%' . $string . '%');
                        }
                    }
                    if ($request->technology && $request->technology != 'undefined') {
                        $q->where('technology_id', '=', $request->technology);
                    }
                    if ($request->experience) {
                        // $experiences = explode(",", $request->experience);
                        //   foreach ($experiences as  $value) {
                        $string =  preg_replace('/\s+/', '', $request->experience);
                        $q->where('experience', '=',  $string);
                       /* if ($string == '5') {
                            $q->whereBetween('experience', [0, 5]);
                        }
                        if ($string == '10') {
                            $q->whereBetween('experience', [5, 10]);
                        }
                        if ($string == '15') {
                            $q->whereBetween('experience', [10, 15]);
                        }
                        if ($string == '20') {
                            $q->whereBetween('experience', [15, 20]);
                        }
                        if ($string == '25') {
                            $q->whereBetween('experience', [20, 25]);
                        }*/

                        //}

                    }
                    if ($request->visaType) {
                        $visaTypes = explode(",", $request->visaType);
                        $q->whereIn('visaType', $visaTypes);
                    }
                    if ($request->tax_type) {
                        $tax_types = explode(",", $request->tax_type);
                        $q->whereIn('tax_type', $tax_types);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->where('user_status', '=', 'p')
                // ->where('keywords', 'like', '%' . $request->param . '%')
                ->whereNotNull('resume')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            //  ->whereIn('consultant_id',$ids)->get();


            //  }else{

            /*       $hotlist = Consultants::with(['user', 'technologies'])
                ->whereHas('technologies', function($q) use($request){
                 $params= explode(",",$request->param);
                         foreach ($params as  $value)
                         {
                         $q->Where('name', 'like', '%' . $value . '%');
                         }
                   })
                 ->get(); */
            //   }



        } else {


            $hotlist = Consultants::with(['user', 'technologies'])->where('user_status', '=', 'p')->orderBy('created_at', 'desc')
                ->whereNotNull('resume')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }
      }
        return response()->json([
            'hotlist' => $hotlist

        ]);
    }
    public function getExportHotList()
    {
        $exportlist = \DB::table('reports')
            ->join('technologies', 'reports.technology_id', '=', 'technologies.technology_id')
            ->select('reports.first_name', 'reports.experience', 'reports.state', 'reports.willingLocation', 'reports.visaType', 'technologies.name as technology')
            ->where('reports.admin_status', '=', 'A')
            ->where('consultant_type', '=', 'A')
            ->get();

        return response()->json([

            'exportlist' => $exportlist
        ]); /*
        $exportlist = \App\Reports::select("*", \DB::raw("CASE technology
WHEN 'others' THEN otherTechnologies ELSE technology END AS technology"))
            ->where('reports.wStatus', '=', 'A')
            ->where('reports.adminStatus', '=', 'A')
            ->orderBy('reports.created_at', 'desc')
            ->get();


        return response()->json(['exportlist' => $exportlist], 200); */
    }
    public function getAllExportVendors()
    {
        $exportlist = \DB::table('vendor_company_contacts')
            ->join('vendor_companies', 'vendor_company_contacts.vendor_company_id', '=', 'vendor_companies.vendor_company_id')
            ->select('vendor_companies.name', 'vendor_company_contacts.contactName', 'vendor_company_contacts.contactEmail','vendor_company_contacts.created_at')
            ->where('vendor_companies.vendor_type', '=', 'V')
            ->where('vendor_company_contacts.userId', '=', \Auth::user()->id)
            
            ->orderBy('vendor_company_contacts.created_at', 'desc')
            ->get();

        return response()->json([

            'exportlist' => $exportlist
        ]); /*
        $exportlist = \App\Reports::select("*", \DB::raw("CASE technology
WHEN 'others' THEN otherTechnologies ELSE technology END AS technology"))
            ->where('reports.wStatus', '=', 'A')
            ->where('reports.adminStatus', '=', 'A')
            ->orderBy('reports.created_at', 'desc')
            ->get();


        return response()->json(['exportlist' => $exportlist], 200); */
    }

    public function getExportPrimeVendors()
    {
        $exportlist = \DB::table('vendor_company_contacts')
            ->join('vendor_companies', 'vendor_company_contacts.vendor_company_id', '=', 'vendor_companies.vendor_company_id')
            ->select('vendor_companies.name', 'vendor_company_contacts.contactName', 'vendor_company_contacts.contactEmail','vendor_company_contacts.created_at')
            ->where('vendor_companies.vendor_type', '=', 'P')
            ->where('vendor_company_contacts.userId', '=', \Auth::user()->id)
            ->orderBy('vendor_company_contacts.created_at', 'desc')
            ->get();

        return response()->json([

            'exportlist' => $exportlist
        ]); /*
        $exportlist = \App\Reports::select("*", \DB::raw("CASE technology
WHEN 'others' THEN otherTechnologies ELSE technology END AS technology"))
            ->where('reports.wStatus', '=', 'A')
            ->where('reports.adminStatus', '=', 'A')
            ->orderBy('reports.created_at', 'desc')
            ->get();


        return response()->json(['exportlist' => $exportlist], 200); */
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'consultant_email' => 'required|unique:reports,consultant_email',
        ]);

        //  $request->validate([
        //       'image' => ['required','mimes:jpg,png,JPEG,jpeg']
        //   ]);

        //  if ($request->hasFile('image')){
        try {
            //code...
            //  $filename = date('His').'-'.$request->file('image')->getClientOriginalName();
            //$path = $request->file('image')->storeAs('public\profile',$filename);
            if (\Auth::user()->getRoleNames()[0] == 'admin' || \Auth::user()->getRoleNames()[0] == 'adminhunters') {
                $user_status = 'p';
            } else {
                $user_status = 'u';
            }


            if (\Auth::user()->getRoleNames()[0] == 'recruiter') {
                $type = 'E';
                $user_status = 'p';
            } else {
                $type = 'I';
            }


            $user = new Consultants();
            $user->type =  $type;
            $user->user_status =  $user_status;
            if ($request->first_name)
                $user->first_name = $request->first_name;
            if ($request->last_name)
                $user->last_name = $request->last_name;
            if ($request->consultant_email)
                $user->consultant_email = $request->consultant_email;
            if ($request->consultant_mobile_number)
                $user->consultant_mobile_number = $request->consultant_mobile_number;
            if ($request->rate)
                $user->rate = $request->rate;
            if ($request->expected_rate)
                $user->expected_rate = $request->expected_rate;
            if ($request->experience)
                $user->experience = $request->experience;
            if ($request->visaType)
                $user->visaType = $request->visaType;
            if ($request->city)
                $user->city = $request->city;
            if ($request->state)
                $user->state = $request->state;
            if ($request->willingLocation)
                $user->willingLocation = $request->willingLocation;
            if ($request->comments)
                $user->comments = $request->comments;
            if ($request->note) {
                $user->note = $request->note;
            }
            if ($request->job_id) {
                $user->job_id = $request->job_id;
            }
            if ($request->technology_id)
                $user->technology_id = $request->technology_id;
            if ($request->consultant_status)
                $user->consultant_status = $request->consultant_status;
            if ($request->resource)
                $user->resource = $request->resource;
            if ($request->ssn)
                $user->ssn = $request->ssn;
            if ($request->availabity)
                $user->availability = $request->availabity;
            if ($request->documentsCollected)
                $user->documentsCollected = $request->documentsCollected;
            if ($request->skypeId)
                $user->skypeId = $request->skypeId;
            if ($request->linkedInUrl)
                $user->linkedInUrl = $request->linkedInUrl;
            if ($request->priority)
                $user->priority = $request->priority;
            if ($request->bestContactNumber)
                $user->bestContactNumber = $request->bestContactNumber;
            if ($request->portal_status)
                $user->portal_status = $request->portal_status;
            if ($request->resume) {
                $user->resume =  $request->resume;
            }
            if ($request->otherDocument) {
                $user->otherDocument = $request->otherDocument;
            }
            if ($request->workAuthorization) {
                $user->workAuthorization = $request->workAuthorization;
            }
            if ($request->schedule_date) {
                $user->schedule_date = Carbon::parse($request->schedule_date);
            }
            if ($request->tax_type) {
                $user->tax_type = $request->tax_type;
            }
            if ($request->vendor_company_name) {
                $user->vendor_company_name = $request->vendor_company_name;
            }
            if ($request->company_email) {
                $user->company_email = $request->company_email;
            }
            if ($request->company_mobile_number) {
                $user->company_mobile_number = $request->company_mobile_number;
            }
            if ($request->keywords) {
                $user->keywords = serialize($request->keywords);
            }
            if ($request->feedback) {
                $user->feedback = $request->feedback;
            }
            if ($request->consultant_type) {
                $user->consultant_type = $request->consultant_type;
            }

            $user->userId = \Auth::user()->id;
            $user->created_at = date('Y-m-d H:i:s');
            $user->save();


            return response()->json([
                'data' => 'created',

                'consultant' => Consultants::with(['user', 'technologies'])->select([
                    '*',
                    \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                ])->find($user->consultant_id)
            ]);
        } catch (\Exception $e) {
            abort(response()->json(["message" => $e->getMessage(), $e->getCode()]));
        }
        //  }
    }
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'consultant_email' => 'required|unique:reports,consultant_email,' . $id . ',consultant_id',
        ]);


        $resumepath = '';
        $workpath = '';
        $otherDocumentpath = '';
        // add user
        $user = \App\Consultants::find($id);
        if ($request->first_name)
            $user->first_name = $request->first_name;
        if ($request->last_name)
            $user->last_name = $request->last_name;
        if ($request->consultant_email)
            $user->consultant_email = $request->consultant_email;
        if ($request->consultant_mobile_number)
            $user->consultant_mobile_number = $request->consultant_mobile_number;
        if ($request->rate)
            $user->rate = $request->rate;
        if ($request->experience)
            $user->experience = $request->experience;
        if ($request->visaType)
            $user->visaType = $request->visaType;
        if ($request->city)
            $user->city = $request->city;
        if ($request->state)
            $user->state = $request->state;
        if ($request->willingLocation)
            $user->willingLocation = $request->willingLocation;
        if ($request->comments)
            $user->comments = $request->comments;
        if ($request->note) {
            $user->note = $request->note;
        }
        if ($request->job_id) {
            $user->job_id = $request->job_id;
        }
        if ($request->expected_rate)
        $user->expected_rate = $request->expected_rate;
        if ($request->technology_id)
            $user->technology_id = $request->technology_id;
        if ($request->consultant_status)
            $user->consultant_status = $request->consultant_status;
        if ($request->resource)
            $user->resource = $request->resource;
        if ($request->ssn)
            $user->ssn = $request->ssn;
        if ($request->availabity)
            $user->availability = $request->availabity;
        if ($request->documentsCollected)
            $user->documentsCollected = $request->documentsCollected;
        if ($request->skypeId)
            $user->skypeId = $request->skypeId;
        if ($request->linkedInUrl)
            $user->linkedInUrl = $request->linkedInUrl;
        if ($request->priority)
            $user->priority = $request->priority;
        if ($request->bestContactNumber)
            $user->bestContactNumber = $request->bestContactNumber;
        if ($request->portal_status)
            $user->portal_status = $request->portal_status;
        if ($request->resume) {
            $user->resume =  $request->resume;
        }
        if ($request->otherDocument) {
            $user->otherDocument = $request->otherDocument;
        }
        if ($request->workAuthorization) {
            $user->workAuthorization = $request->workAuthorization;
        }
        if ($request->schedule_date) {
            $user->schedule_date = Carbon::parse($request->schedule_date);
        }
        if ($request->tax_type) {
            $user->tax_type = $request->tax_type;
        }
        if ($request->vendor_company_name) {
            $user->vendor_company_name = $request->vendor_company_name;
        }
        if ($request->company_email) {
            $user->company_email = $request->company_email;
        }
        if ($request->company_mobile_number) {
            $user->company_mobile_number = $request->company_mobile_number;
        }
        if ($request->keywords) {
            $user->keywords = serialize($request->keywords);
        }
        if ($request->feedback) {
            $user->feedback = $request->feedback;
        }
        if ($request->consultant_type) {
            $user->consultant_type = $request->consultant_type;
        }
        $user->save();

        return response()->json([
            'data' => 'Updated',

            'consultant' => Consultants::with(['user', 'technologies'])->select([
                '*',
                \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
            ])->find($id)
        ]);
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
        $user = \App\Consultants::with(['user', 'technologies'])->find($id);

        return response()->json(['user' => $user, 'path' => storage_path("app/uploads/resume")], 200);
    }
    public function edit($id)
    {

        $user = \App\Consultants::with(['user', 'technologies'])->find($id);
        return response()->json(['user' => $user, 'path' => storage_path("app/uploads/resume")], 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function saveDocument(Request $request)
    {
        /* $this->validate($request, [
            'duration' => 'required'
        ]); */


        $otherDocumentpath = '';

        if ($request->hasFile('resume')) {

            $filenameWithExt = $request->file('resume')->getClientOriginalName();
            //Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('resume')->getClientOriginalExtension();
            // Filename to store
            $resumepath = $filename . '_' . time() . '.' . $extension;
            // Upload Image
            $path = $request->file('resume')->storeAs('uploads/resume', $resumepath);

            return response()->json(['path' => $resumepath], 200);
        }

        if ($request->hasFile('otherDocument')) {

            $filenameWithExt = $request->file('otherDocument')->getClientOriginalName();
            //Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('otherDocument')->getClientOriginalExtension();
            // Filename to store
            $otherDocumentpath = $filename . '_' . time() . '.' . $extension;
            // Upload Image
            $path = $request->file('otherDocument')->storeAs('uploads/otherDocument', $otherDocumentpath);

            return response()->json(['path' => $otherDocumentpath], 200);
        }

        if ($request->hasFile('workAuthorization')) {

            $filenameWithExt = $request->file('workAuthorization')->getClientOriginalName();
            //Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('workAuthorization')->getClientOriginalExtension();
            // Filename to store
            $workpath = $filename . '_' . time() . '.' . $extension;
            // Upload Image
            $path = $request->file('workAuthorization')->storeAs('uploads/workauthorization', $workpath);

            return response()->json(['path' => $workpath], 200);
        }

        return response()->json(['user' => "Error"], 400);
    }
    public function statusChange(Request $request)
    {
        $consultant = '';
        if ($request->consultant_id) {
            $consultant = \App\Consultants::find($request->consultant_id);
            $consultant->consultant_status = $request->consultant_status;
            if ($request->schedule_date)
                $consultant->schedule_date = Carbon::parse($request->schedule_date)->format('Y-m-d H:i:s');
            if ($request->timezone)
                $consultant->timezone = $request->timezone;
            if ($request->feedback)
                $consultant->feedback = $request->feedback;
            $consultant->save();
        }





        return response()->json(['consultant' => $consultant], 200);
    }

    public function statushotlist(Request $request)
    {
        $consultant = '';
        if ($request->consultant_id) {
            $consultant = \App\Consultants::find($request->consultant_id);
            if ($request->admin_status)
                $consultant->admin_status = $request->admin_status;
            if ($request->user_status)
                $consultant->user_status = $request->user_status;

            $consultant->save();
        }



        //
        if (\Auth::user()->getRoleNames()[0] == 'admin' || \Auth::user()->getRoleNames()[0] == 'adminhunters') {

            $where = [];
            // $where['userId'] = \Auth::user()->id;
            $where['admin_status'] = 'D';
            return response()->json([

                'consultants' => Consultants::with([
                    'user', 'technologies', 'withJobs' => function ($q) {
                        $q->with('user');
                    }
                    //'submissions' => function ($q) {


                    //  $q->with(['user_details','clients','contactList'=> function ($q) {


                    //  $q->with(['companies']);
                    // }
                    //  ]);
                    // }
                ])->select([
                    '*',
                    \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                ])
                    ->where($where)
                    ->orderBy('created_at', 'DESC')
                    ->where('user_status', '=', 'p')
                    ->get()
            ]);
        } else {
            return response()->json([

                'consultants' => Consultants::with(['user', 'technologies', 'withJobs' => function ($q) {
                    $q->with('user');
                }, 'submissions' => function ($q) {


                    $q->with(['user_details', 'clients', 'contactList' => function ($q) {


                        $q->with(['companies']);
                    }]);
                }])->select([
                    '*',
                    \DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name, '')) as name")
                ])
                    ->where('userId', '=', \Auth::user()->id)
                    ->where('user_status', '=', 'u')
                    //  ->where('admin_status', '=', 'D')
                    ->orderBy('created_at', 'DESC')->get()
            ]);
        }
    }
}
