<?php

namespace App\Http\Controllers\Admin;

use App\Consultants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Clients;
use App\Profile;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $technologies = Clients::with('user')->orderBy('name', 'ASC')->get();

        return response()->json([
                                 'clients' => $technologies
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

              $data =  Clients::updateOrCreate(

                    ['client_id' => $request->client_id],
                    [
                        'name' =>$request->name,
                        'userId'=>\Auth::user()->id
                    ]
                );

                return response()->json(['data' => 'created',

                'client' => Clients::with('user')->find($data->client_id)
               ]);
            } catch (\Exception $e) {
                abort(response()->json(["message" => $e->getMessage(), $e->getCode()]));
            }
      //  }
    }
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|unique:clients,name,' . $id . ',client_id',
        ]);


        $resumepath = '';
        $workpath = '';
        $otherDocumentpath = '';
        // add user
        $user = \App\Clients::find($id);
        $user->name = $request->name;
            $user->save();


        return response()->json(['data' => 'Updated',

                'client' => Clients::with('user')->find($user->client_id)
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
