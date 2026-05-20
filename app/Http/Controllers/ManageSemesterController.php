<?php

namespace App\Http\Controllers;

use App\Models\semesters;
use App\Models\semesterStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManageSemesterController extends Controller
{
    /**
     * Display a listing of semester with statuses and search bar filters.
     */
    public function index(Request $request)
{
    try {

        $query = semesters::with(['status', 'academicYear']);

        if ($request->search) {

            // 1. Normalize input
            $search = strtolower($request->search);
            $search = str_replace([',', '-'], ' ', $search);
            $search = preg_replace('/\s+/', ' ', $search);
            $search = trim($search);

            // 2. Break into keywords
            $keywords = explode(' ', $search);

            // 3. Smart multi-word matching
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->whereRaw('LOWER(code) LIKE ?', ["%{$word}%"])
                             ->orWhereRaw('LOWER(name) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        }

        if ($request->statusId) {
            $query->where('statusId', $request->statusId);
        }

        $semesters = $query->orderBy('dateStarted', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $semesters->map(function ($s) {
                return [
                    'id'               => $s->id,
                    'code'             => $s->code,
                    'semesterName'     => $s->name,

                    'academicYearId'   => $s->academicYearId,
                    'academicYear'     => $s->academicYear?->name,

                    'startDate'        => $s->dateStarted,
                    'endDate'          => $s->dateEnd,

                    'statusId'         => $s->statusId,
                    'status'           => $s->status?->status,
                ];
            })
        ]);

    } catch (\Exception $e) {

        Log::error('Semester INDEX error', [
            'message' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}

    /**
     * Creates and stores new semester.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'code'         => 'required|string',
                'semesterName' => 'required|string',
                'academicYearId' => 'required|integer',
                'startDate'    => 'required|date',
                'endDate'      => 'required|date|after_or_equal:startDate',
                'statusId'     => 'required',
            ]);

            // Manual unique check (avoids schema prefix issue with Rule::unique)
            $exists = semesters::where('code', $request->code)->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code already exists.'
                ], 422);
            }

            $semester = semesters::create([
                'code'           => $request->code,
                'name'           => $request->semesterName,
                'academicYearId' => $request->academicYearId,
                'dateStarted'    => $request->startDate,
                'dateEnd'        => $request->endDate,
                'statusId'       => $request->statusId,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $semester
            ]);

        } catch (\Exception $e) {

            Log::error('Semester STORE error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }

    /**
     * Update records in the database.
     */
    public function update(Request $request, $id)
    {
        try {

            $semester = semesters::findOrFail($id);

            $request->validate([
                'code'         => 'required|string',
                'semesterName' => 'required|string',
                'startDate'    => 'required|date',
                'endDate'      => 'required|date|after_or_equal:startDate',
                'statusId'     => 'required',
            ]);

            // Manual unique check, excluding current record
            $exists = semesters::where('code', $request->code)
                                ->where('id', '!=', $id)
                                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code already exists.'
                ], 422);
            }

            $semester->update([
                'code'        => $request->code,
                'name'        => $request->semesterName,
                'dateStarted' => $request->startDate,
                'dateEnd'     => $request->endDate,
                'statusId'    => $request->statusId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Semester updated successfully',
                'data'    => $semester
            ]);

        } catch (\Exception $e) {

            Log::error('Semester UPDATE error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }

    /**
     * Removes semester by deleting the record from the database.
     */
    public function destroy($id)
    {
        try {

            $semester = semesters::findOrFail($id);
            $semester->delete();

            return response()->json([
                'success' => true,
                'message' => 'Semester deleted successfully'
            ]);

        } catch (\Exception $e) {

            Log::error('Semester DESTROY error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
}
