<?php

namespace App\Http\Controllers\Admin;

use App\Consultants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Contacts;
use App\Profile;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $contacts = Contacts::with('companies')->orderBy('contactName', 'ASC')->get();

        return response()->json([
                                 'contacts' => $contacts
                                ]);
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
       $request->validate([
           'vendor_company_id' => ['required']
       ]);

      //  if ($request->hasFile('image')){
            try {
                //code...
              //  $filename = date('His').'-'.$request->file('image')->getClientOriginalName();
                //$path = $request->file('image')->storeAs('public\profile',$filename);

              $data =  Contacts::Create(
                    [
                        'vendor_company_id' =>$request->vendor_company_id,
                        'contactName' =>$request->contactName,
                        'contactMobile' =>$request->contactMobile,
                        'userId'=>\Auth::user()->id,
                        'contactEmail' =>$request->contactEmail

                    ]
                );

                return response()->json(['data' => 'created',

                'contact' => Contacts::with('companies')->find($data->vendor_company_contact_id)
               ]);
            } catch (\Exception $e) {
                abort(response()->json(["message" => $e->getMessage(), $e->getCode()]));
            }
      //  }
    }
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'contactEmail' => 'required|unique:vendor_company_contacts,contactEmail,' . $id . ',vendor_company_contact_id',
        ]);


        $resumepath = '';
        $workpath = '';
        $otherDocumentpath = '';
        // add user
        $user = \App\Contacts::find($id);
        $user->vendor_company_id = $request->vendor_company_id;
        $user->contactName = $request->contactName;
        $user->contactMobile = $request->contactMobile;
        $user->contactEmail = $request->contactEmail;
            $user->save();


        return response()->json(['data' => 'Updated',

                'contact' => Contacts::with('companies')->find($user->vendor_company_contact_id)
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
        $contacts = \App\Contacts::where('vendor_company_id','=',$id)->get();
        $vendorContactslist = [];
        $vendorContactslist[] =["label"=>"Choosse Consultant","value"=>""];
        foreach($contacts as $value)
        {
                $vendorContactslist[] =["label"=>$value->contactEmail,"value"=>$value->vendor_company_contact_id];

        }
        return response()->json(['contacts' => $vendorContactslist], 200);
    }
    public function edit($id)
    {

        $contacts = \App\Contacts::where('vendor_company_id','=',$id)->get();
        $vendorContactslist = [];
        $vendorContactslist[] =["label"=>"Choosse Email","value"=>""];
        foreach($contacts as $value)
        {
                $vendorContactslist[] =["label"=>$value->contactEmail,"value"=>$value->vendor_company_contact_id];

        }
        return response()->json(['contacts' => $vendorContactslist], 200);
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
    public function getDetails(Request $request)
    {
        $contacts = \App\Contacts::find($request->index);
        return response()->json(['contactDetails' => $contacts], 200);
    }
}
