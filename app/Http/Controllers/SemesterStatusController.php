<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\semesterStatus;
use Illuminate\Support\Facades\Log;

class SemesterStatusController extends Controller
{
    public function getStatuses()
{
    try {
        $statuses = \App\Models\semesterStatus::orderBy('status', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $statuses->map(function ($s) {
                return [
                    'id' => $s->id,
                    'status' => $s->status,
                ];
            })
        ]);

    } catch (\Exception $e) {
        Log::error('Semester STATUS error', [
            'message' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}
}
