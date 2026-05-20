<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYears;

class ManageAcademicYearController extends Controller
{
    /**
     * Display a list
     */
public function index(Request $request)
{
    Log::info('AcademicYear INDEX called');

    try {
        $query = AcademicYears::select('id', 'startDate', 'endDate', 'code', 'name');

        if ($search = trim($request->query('search', ''))) {

            $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($terms as $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ilike', "%{$term}%")
                      ->orWhere('code', 'ilike', "%{$term}%")
                      ->orWhereRaw("TO_CHAR(\"startDate\", 'YYYY-MM-DD') ILIKE ?", ["%{$term}%"])
                      ->orWhereRaw("TO_CHAR(\"endDate\",   'YYYY-MM-DD') ILIKE ?", ["%{$term}%"])
                      ->orWhereRaw("TO_CHAR(\"startDate\", 'DD Month YYYY') ILIKE ?", ["%{$term}%"])
                      ->orWhereRaw("TO_CHAR(\"endDate\",   'DD Month YYYY') ILIKE ?", ["%{$term}%"]);
                });
            }
        }

        $data = $query->orderBy('startDate', 'asc')->get();

        Log::info('AcademicYear INDEX success', ['count' => $data->count()]);

        return $data;

    } catch (\Exception $e) {
        Log::error('AcademicYear INDEX failed', ['error' => $e->getMessage()]);
        throw $e;
    }
}
    /**
     * Store
     */
    public function store(Request $request)
    {
        Log::info('AcademicYear STORE called', [
            'request' => $request->all()
        ]);

        try {

            $validated = $request->validate([
                'code'      => ['required', 'string', 'max:10'],
                'name'      => ['required', 'string', 'max:255'],
                'startDate' => ['required', 'date'],
                'endDate'   => ['required', 'date', 'after:startDate'],
            ]);

     // Manual unique check for PostgreSQL schema-qualified table
            $exists = DB::connection('pgsql')
                ->table('academics.academicYears')
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => ['code' => ['This code has already been taken.']]
                ], 422);
            }

            Log::info('AcademicYear STORE validated', [
                'validated' => $validated
            ]);

            $academicYear = AcademicYears::create($validated);

            Log::info('AcademicYear STORE success', [
                'id' => $academicYear->id ?? null
            ]);

            return response()->json([
                'message' => 'Academic year created successfully',
                'data'    => $academicYear
            ], 201);

        } catch (\Throwable $e) {

            Log::error('AcademicYear STORE failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        Log::info('AcademicYear UPDATE called', [
            'id'      => $id,
            'request' => $request->all()
        ]);

        try {

            $academicYear = AcademicYears::findOrFail($id);

            Log::info('AcademicYear found for update', [
                'existing' => $academicYear
            ]);

            $validated = $request->validate([
                'code'      => ['required', 'string', 'max:10'],
                'name'      => ['required', 'string', 'max:255'],
                'startDate' => ['required', 'date'],
                'endDate'   => ['required', 'date', 'after:startDate'],
            ]);

            // Manual unique check excluding current record done bcz schema is being detected as an db by laravel through the model
            $exists = DB::connection('pgsql')
                ->table('academics.academicYears')
                ->where('code', $request->code)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => ['code' => ['This code has already been taken.']]
                ], 422);
            }

            Log::info('AcademicYear UPDATE validated', [
                'validated' => $validated
            ]);

            $academicYear->update($validated);

            Log::info('AcademicYear UPDATE success', [
                'id' => $id
            ]);

            return response()->json([
                'message' => 'Academic year updated successfully',
                'data'    => $academicYear
            ]);

        } catch (\Throwable $e) {

            Log::error('AcademicYear UPDATE failed', [
                'error' => $e->getMessage(),
                'id'    => $id
            ]);

            throw $e;
        }
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        Log::info('AcademicYear DELETE called', [
            'id' => $id
        ]);

        try {

            $academicYear = AcademicYears::findOrFail($id);

            $academicYear->delete();

            Log::info('AcademicYear DELETE success', [
                'id' => $id
            ]);

            return response()->json([
                'message' => 'Academic year deleted successfully'
            ]);

        } catch (\Throwable $e) {

            Log::error('AcademicYear DELETE failed', [
                'error' => $e->getMessage(),
                'id'    => $id
            ]);

            throw $e;
        }
    }
}