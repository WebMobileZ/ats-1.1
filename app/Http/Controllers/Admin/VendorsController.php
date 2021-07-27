<?php

namespace App\Http\Controllers\Admin;

use App\Consultants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\PrimeVendors;
use App\Profile;

class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $primevendors = PrimeVendors::with(['user','contacts'])->where('vendor_type','=','V')->orderBy('created_at', 'DESC')->get();

        return response()->json([
                                 'primevendors' => $primevendors
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
      //  $request->validate([
     //       'image' => ['required','mimes:jpg,png,JPEG,jpeg']
     //   ]);

      //  if ($request->hasFile('image')){
            try {
                //code...
              //  $filename = date('His').'-'.$request->file('image')->getClientOriginalName();
                //$path = $request->file('image')->storeAs('public\profile',$filename);

              $data =  PrimeVendors::updateOrCreate(

                    ['vendor_company_id' => $request->prime_vendor_id],
                    [
                        'name' =>$request->name,
                        'userId'=>\Auth::user()->id,
                        'vendor_type'=>'V',
                        'linkedin_url'=>$request->linkedin_url,
                        'numberofemployees'=>$request->numberofemployees
                    ]
                );

                return response()->json(['data' => 'created',

                'primevendor' => PrimeVendors::with(['user','contacts'])->find($data->vendor_company_id)
               ]);
            } catch (\Exception $e) {
                abort(response()->json(["message" => $e->getMessage(), $e->getCode()]));
            }
      //  }
    }
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|unique:vendor_companies,name,' . $id . ',vendor_company_id',
        ]);


        $resumepath = '';
        $workpath = '';
        $otherDocumentpath = '';
        // add user
        $user = \App\PrimeVendors::find($id);
        $user->name = $request->name;
        if($request->linkedin_url)
        $user->linkedin_url = $request->linkedin_url;
        if($request->numberofemployees)
        $user->numberofemployees = $request->numberofemployees;
        $user->save();


        return response()->json(['data' => 'Updated',

                'primevendor' => PrimeVendors::with(['user','contacts'])->find($user->vendor_company_id)
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
}
