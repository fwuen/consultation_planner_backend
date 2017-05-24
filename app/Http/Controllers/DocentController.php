<?php

namespace App\Http\Controllers;

use App\Docent;
use Illuminate\Http\Request;

//TODO: was passiert, wenn die Validierung ergibt, dass die Daten nicht korrekt sind? --> irgendwie behandeln?
class DocentController extends Controller
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
        /*
         * Zeigt im Frontend das entsprechende Formular zum Erzeugen der neuen Ressource
         * Dozenten müssen allerdings nicht über ein Formular erzeugt werden
         * Dozenten werden nach dem ersten Login mit dem SSO-Dienst in der Datenbank abgelegt
         * Daher wird hier keine view zurückgegeben
         */
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function edit(Docent $docent)
    {
        //Analog zu create()
        //Wird nicht benötigt
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

        $docents = DB::table('docents')
            ->where(function ($query) use($termArray) {
                foreach ($termArray as $value) {
                    $query->orWhere('lastname', 'like', '%'.$value.'%');
                    $query->orWhere('firstname', 'like', '%'.$value.'%');
                }
            })->get();

        return $docents;
    }
}
