<?php

namespace App\Http\Controllers;

use App\MeetingSeries;
use Illuminate\Http\Request;

class MeetingSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Wird nicht benötigt
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Wird nicht benötigt
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
            'docent_id' => 'required|max:10'
        ]);

        $meetingseries = new MeetingSeries;
        $meetingseries->docent_id = $request->get('docent_id');

        $meetingseries.save();
        return redirect()->route('/');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MeetingSeries  $meetingSeries
     * @return \Illuminate\Http\Response
     */
    public function show(MeetingSeries $meetingSeries)
    {
        return response()->json([
            'id' => $meetingSeries->id,
            'docent_id' => $meetingSeries->docent_id
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MeetingSeries  $meetingSeries
     * @return \Illuminate\Http\Response
     */
    public function edit(MeetingSeries $meetingSeries)
    {
        //nicht benötigt
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MeetingSeries  $meetingSeries
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MeetingSeries $meetingSeries)
    {
        $this->validate($request, [
            'docent_id' => 'required|max:10'
        ]);
        $meetingSeries->docent_id = $request->get('docent_id');
        $meetingSeries.save();
        return redirect()->route('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MeetingSeries  $meetingSeries
     * @return \Illuminate\Http\Response
     */
    public function destroy(MeetingSeries $meetingSeries)
    {
        $meetingSeries->delete();
        return redirect()->route('/');
    }
}
