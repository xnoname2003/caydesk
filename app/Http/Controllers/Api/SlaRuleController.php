<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlaRule;

class SlaRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slaRules = SlaRule::orderBy('name', 'asc')->get(['id', 'name', 'priority.name', 'response_time_hours', 'resolution_time_hours']);
        
        return response()->json([
            'message' => 'SLA rules retrieved successfully.',
            'data' => $slaRules
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
