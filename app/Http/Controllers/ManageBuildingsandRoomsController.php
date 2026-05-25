<?php

namespace App\Http\Controllers;

use App\Models\building;
use App\Models\room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageBuildingsandRoomsController extends Controller
{
   
    // BUILDINGS
  

  
    public function index()
    {
        $buildings = building::select('id', 'name', 'code')
            ->with(['rooms:id,name,buildingId,code'])
            ->get();

        return response()->json($buildings);
    }

   
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $term = '%' . mb_strtolower($q) . '%';

        // Buildings matching the query
        $buildings = building::select('id', 'name', 'code')
            ->whereRaw('LOWER(name) LIKE ?', [$term])
            ->orWhereRaw('LOWER(code) LIKE ?', [$term])
            ->limit(10)
            ->get()
            ->map(fn($b) => [
                'type'   => 'building',
                'id'     => $b->id,
                'label'  => "{$b->code} — {$b->name}",
                'code'   => $b->code,
                'name'   => $b->name,
            ]);

        // Rooms matching the query (with their building info joined in one query)
        $rooms = room::select('rooms.id', 'rooms.name', 'rooms.code', 'rooms.buildingId',
                              'buildings.name as buildingName', 'buildings.code as buildingCode')
            ->join('buildings', 'buildings.id', '=', 'rooms.buildingId')
            ->where(function ($query) use ($term) {
                $query->whereRaw('LOWER(rooms.name) LIKE ?', [$term])
                      ->orWhereRaw('LOWER(rooms.code) LIKE ?', [$term])
                      ->orWhereRaw('LOWER(buildings.name) LIKE ?', [$term])
                      ->orWhereRaw('LOWER(buildings.code) LIKE ?', [$term]);
            })
            ->limit(20)
            ->get()
            ->map(fn($r) => [
                'type'         => 'room',
                'id'           => $r->id,
                'label'        => "{$r->code} — {$r->name} ({$r->buildingCode})",
                'code'         => $r->code,
                'name'         => $r->name,
                'buildingId'   => $r->buildingId,
                'buildingName' => $r->buildingName,
                'buildingCode' => $r->buildingCode,
            ]);

        // Merge, buildings first, then rooms — capped at 30 total
        $results = $buildings->concat($rooms)->take(30)->values();

        return response()->json($results);
    }

  
    public function storeBuilding(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        $exists = building::where('code', $request->code)->exists();
        if ($exists) {
            return response()->json([
                'message' => 'The building code has already been taken.',
                'errors'  => ['code' => ['The building code has already been taken.']]
            ], 422);
        }

        $building = building::create([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        $building->load('rooms');

        return response()->json($building, 201);
    }

  
    public function updateBuilding(Request $request, $id)
    {
        $building = building::findOrFail($id);

        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        $exists = building::where('code', $request->code)
                          ->where('id', '!=', $id)
                          ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'The building code has already been taken.',
                'errors'  => ['code' => ['The building code has already been taken.']]
            ], 422);
        }

        $building->update([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return response()->json($building);
    }

  
    public function destroyBuilding($id)
    {
        $building = building::findOrFail($id);
        $building->rooms()->delete();
        $building->delete();

        return response()->json(['message' => 'Building deleted']);
    }


    // ROOMS
   


    public function storeRoom(Request $request, $buildingId)
    {
        $building = building::findOrFail($buildingId);

        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        $room = $building->rooms()->create([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return response()->json($room, 201);
    }

    public function updateRoom(Request $request, $buildingId, $roomId)
    {
        $room = room::where('buildingId', $buildingId)->findOrFail($roomId);

        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        $room->update([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return response()->json($room);
    }

  
    public function destroyRoom($buildingId, $roomId)
    {
        $room = room::where('buildingId', $buildingId)->findOrFail($roomId);
        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}