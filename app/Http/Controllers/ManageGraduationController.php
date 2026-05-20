<?php

namespace App\Http\Controllers;

use App\Models\graduation;
use Illuminate\Http\Request;

class ManageGraduationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $query = graduation::with('graduationsmester');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('date', 'like', "%{$search}%")
              ->orWhereHas('graduationsmester', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    $graduations = $query->get()->map(function ($graduation) {
        return [
            'id'            => $graduation->id,
            'date'          => $graduation->date,
            'description'   => $graduation->description,
            'semesterId'    => $graduation->semesterId,
            'semester_name' => $graduation->graduationsmester->name ?? null,
        ];
    });

    return response()->json([
        'success' => true,
        'data'    => $graduations
    ], 200);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'semesterId' => 'required|integer',
        ]);

        $graduation = graduation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Graduation created successfully',
            'data' => $graduation
        ], 201);
    }

   
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $graduation = graduation::find($id);

        if (!$graduation) {
            return response()->json([
                'success' => false,
                'message' => 'Graduation not found'
            ], 404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'semesterId' => 'required|integer',
        ]);

        $graduation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Graduation updated successfully',
            'data' => $graduation
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $graduation = graduation::find($id);

        if (!$graduation) {
            return response()->json([
                'success' => false,
                'message' => 'Graduation not found'
            ], 404);
        }

        $graduation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Graduation deleted successfully'
        ], 200);
    }
}