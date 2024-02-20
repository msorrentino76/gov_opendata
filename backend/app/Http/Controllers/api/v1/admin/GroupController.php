<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Group::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|unique:groups',
        ]);        

        return Group::create( $request->post() );

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Group::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name'  => 'required|unique:groups',
        ]);        

        $data = $request->post();
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['deleted_at']);
        
        return Group::where('id', $id)->update($data);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Group::destroy($id);
    }
}
