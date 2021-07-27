<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Submissions;
use App\Companies;
use App\Contacts;
use App\Consultants;
use App\Clients;
use Carbon\Carbon;
use Auth;

class VendorCompanyContactListController extends Controller
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $vendorList = Companies::with('contacts')->orderBy('created_at', 'DESC')
                            ->get();

        return response()->json(['data'=>$vendorList], 200);
    }


    /**$journal
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->type =="Prime Vendor")
        {
            $this->validate($request, [
                'name'     => 'required|unique:vendor_companies,name',
                'linkedin_url'     => 'required',
                'numberofemployees'     => 'required',
                  'contactEmail'    => 'required|email|unique:vendor_company_contacts,contactEmail',
                  'contactMobile' => 'required',
              ]);
        }else{
            $this->validate($request, [
                  'name'     => 'required|unique:vendor_companies,name',
                  'contactEmail'    => 'required|email|unique:vendor_company_contacts,contactEmail',
                  'contactMobile' => 'required',
              ]);
        }


        $this->validate($request, [
            'name'     => 'required|unique:vendor_companies,name',
            'contactEmail'    => 'required|email|unique:vendor_company_contacts,contactEmail',
            'contactMobile' => 'required',
        ]);
        $type= 'V';
        if($request->type)
        {
        if($request->type =="Prime Vendor")
        {
            $type= 'P';
        }
        if($request->type =="Implementation Partner")
        {
            $type= 'I';
        }
        }
            $vendorList = new \App\Companies();
            $vendorList->name = $request->name;
            $vendorList->userId = \Auth::user()->id;
            $vendorList->created_at = date('Y-m-d H:i:s');
            if($request->type)
            {
                $vendorList->vendor_type =   $type;
            }
            if($request->linkedin_url)
            {
                $vendorList->linkedin_url =   $request->linkedin_url;
            }
            if($request->numberofemployees)
            {
                $vendorList->numberofemployees =  $request->numberofemployees;
            }

            $vendorList->save();
            $contact = new \App\Contacts();
            $contact->contactName = $request->contactName;
            $contact->contactMobile	= $request->contactMobile;
            $contact->contactEmail = $request->contactEmail;
            $contact->ext = $request->ext;
            $contact->userId = \Auth::user()->id;
            $contact->vendor_company_id = $vendorList->vendor_company_id;
            $contact->created_at = date('Y-m-d H:i:s');
            $contact->save();
            $vendorCompanyConatcts = Companies::with('contacts')->find($vendorList->vendor_company_id);

     return response()->json(['data' =>$vendorCompanyConatcts], 200);

    }

    public function show($id)
    {
        $vendorCompanyConatcts = Companies::with('contacts')->find($id);

        return response()->json(['data' => $vendorCompanyConatcts], 200);
    }
    public function edit($id)
    {
        $vendorCompanyConatcts = Companies::with('contacts')->find($id);

        return response()->json(['data' => $vendorCompanyConatcts], 200);
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




    }

}
