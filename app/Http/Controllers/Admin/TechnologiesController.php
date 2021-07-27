<?php

namespace App\Http\Controllers\Admin;

use App\Consultants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Technologies;
use App\Profile;

class TechnologiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $technologies = Technologies::select([
            '*',
            \DB::raw("CONCAT(COALESCE(name, ''),' ','') as label,CONCAT(COALESCE(technology_id, ''),' ','') as value")
        ])->orderBy('name', 'ASC')->get();

        return response()->json([
                                 'technologies' => $technologies
                                ]);
    }
    public function getOnlyTechnologies()
    {

        $technologies = Technologies::orderBy('name', 'ASC')->get();

        return response()->json([
                                 'technologies' => $technologies
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
        $this->validate($request, [
            'name' => 'required|unique:technologies,name'
        ]);
      //  $request->validate([
     //       'image' => ['required','mimes:jpg,png,JPEG,jpeg']
     //   ]);

      //  if ($request->hasFile('image')){
            try {
                //code...
              //  $filename = date('His').'-'.$request->file('image')->getClientOriginalName();
                //$path = $request->file('image')->storeAs('public\profile',$filename);

              $data =  Technologies::updateOrCreate(

                    ['technology_id' => $request->technology_id],
                    [
                        'name' =>$request->name,
                        'userId'=>\Auth::user()->id
                    ]
                );

                return response()->json(['data' => 'created',

                'technology' => Technologies::find($data->technology_id)
               ]);
            } catch (\Exception $e) {
                abort(response()->json(["message" => $e->getMessage(), $e->getCode()]));
            }
      //  }
    }
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|unique:technologies,name,' . $id . ',technology_id',
        ]);


        $resumepath = '';
        $workpath = '';
        $otherDocumentpath = '';
        // add user
        $user = \App\Technologies::find($id);
        $user->name = $request->name;

        $user->updated_by = \Auth::user()->id;
            $user->save();


        return response()->json(['data' => 'Updated',

                'technology' => Technologies::find($user->technology_id)
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
