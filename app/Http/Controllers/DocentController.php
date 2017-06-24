<?php

namespace App\Http\Controllers;

use App\Docent;
use Illuminate\Http\Request;

class DocentController extends Controller
{
    public function show(Docent $docent)
    {
        return response()->json($docent);
    }

    public function store(Request $request)
    {
        $this->doBasicDocentValidation($request);
        $docent = new Docent;
        $this->setAndSaveDocentProperties($docent, $request);
        return redirect('docent/' . $docent->id);
    }

    public function update(Request $request, Docent $docent)
    {
        $this->doBasicDocentValidation($request);
        $this->setAndSaveDocentProperties($docent, $request);
        return redirect('docent/' . $docent->id);
    }

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

    private function setAndSaveDocentProperties(Docent $docent, Request $request)
    {
        $docent->firstname = $request->get('firstname');
        $docent->lastname = $request->get('lastname');
        $docent->email = $request->get('email');
        $docent->academic_title = $request->get('academic_title');
        $docent->save();
    }
}
