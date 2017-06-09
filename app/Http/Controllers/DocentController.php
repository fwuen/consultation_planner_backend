<?php

namespace App\Http\Controllers;

use App\Docent;
use Illuminate\Http\Request;

//TODO: was passiert, wenn die Validierung ergibt, dass die Daten nicht korrekt sind? --> irgendwie behandeln?
class DocentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Speichert die mit create() erzeugte Methode persistent
        $this->validate($request,[
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255',
            'academic_title' => 'required|max:50'
        ]);

        $docent = new Docent;
        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');

        $docent->save();
        return redirect()->route('/');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function show(Docent $docent)
    {
        return response()->json($docent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Docent $docent)
    {
        //Analog zu store()
        $this->validate($request,[
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255',
            'academic_title' => 'required|max:50'
        ]);

        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');

        $docent->save();
        return redirect()->route('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Docent $docent)
    {
        //TODO: Redirect eventuell überarbeiten bzw. ist dieser überhaupt nötig? --> muss in diesem Fall auch in allen anderen Ressource-Controllern geändert werden
        $docent->delete();
        return redirect()->route('/');
    }

    /**
     * Search for specific resources by term.
     *
     * @param  String  $term
     * @return \Illuminate\Support\Collection $docents
     */
    public function search($term)
    {
        $termArray = explode(" ", $term);

        $docents = \DB::table('docents')
            ->where(function ($query) use($termArray) {
                foreach ($termArray as $value) {
                    $query->orWhere('lastname', 'like', '%'.$value.'%');
                    $query->orWhere('firstname', 'like', '%'.$value.'%');
                }
            })->get();

        return response()->json($docents);
    }

    public function getMeetingsByDocent($id)
    {

    }

    public function createMeeting($id)
    {

    }

    public function updateMeeting($id)
    {

    }

    public function getNotificationsByDocent($id)
    {

    }
}
