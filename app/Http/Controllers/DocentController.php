<?php

namespace App\Http\Controllers;

use App\Docent;
use App\Meeting;
use App\MeetingSeries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->doBasicDocentValidation($request);

        $docent = new Docent;
        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');

        $docent->save();

        return redirect('docent/' . $docent->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Docent $docent
     * @return \Illuminate\Http\Response
     */
    public function show(Docent $docent)
    {
        return response()->json($docent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Docent $docent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Docent $docent)
    {
        $this->doBasicDocentValidation($request);

        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');
        $docent->save();
        return redirect('docent/' . $docent->id);
    }

    /**
     * Search for specific resources by term.
     *
     * @param  String $term
     * @return \Illuminate\Support\Collection $docents
     */
    public function search($term)
    {
        $termArray = explode(" ", $term);

        $docents = \DB::table('docents')
            ->where(function ($query) use ($termArray) {
                foreach ($termArray as $value) {
                    $query->orWhere('lastname', 'like', '%' . $value . '%');
                    $query->orWhere('firstname', 'like', '%' . $value . '%');
                }
            })->get();

        return response()->json($docents);
    }

    private function doBasicDocentValidation(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255|unique:docents',
            'academic_title' => 'required|max:50'
        ]);
    }
}
