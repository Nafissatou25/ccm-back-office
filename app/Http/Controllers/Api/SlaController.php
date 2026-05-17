<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sla;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    public function index()
    {
        return Sla::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'response_time' => 'required|integer',
            'resolution_time' => 'required|integer',
        ]);

        return Sla::create($request->all());
    }

    public function update(Request $request, Sla $sla)
    {
        $sla->update($request->all());

        return $sla;
    }

    public function destroy(Sla $sla)
    {
        $sla->delete();

        return response()->json(['message' => 'Deleted']);
    }
}