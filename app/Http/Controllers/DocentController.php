<?php

namespace App\Http\Controllers;

use App\Docent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //NUR EIN TEST: return response()->json(['id' => '1', 'firstname' => 'Test', 'lastname' => 'test', 'email' => 'test@test.de']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        echo 'create';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        echo 'store';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function show(Docent $docent)
    {
        return response()->json([
            'id' => $docent->id,
            'firstname' => $docent->firstname,
            'lastname' => $docent->lastname,
            'email' => $docent->email
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function edit(Docent $docent)
    {
        echo 'edit';
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
        echo 'update';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Docent  $docent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Docent $docent)
    {
        echo 'destroy';
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
