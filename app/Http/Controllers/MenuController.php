<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ModuleFeatures;

class MenuController extends Controller
{
   public function userMenu(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Debug user
        \Log::info('User ID: ' . $user->id);

        // Get the user's roleId from userModuleFeatures
        $userRole = \DB::table('userModuleFeatures')
            ->where('userAccountId', $user->id)
            ->whereNotNull('roleId')
            ->value('roleId');


        // $userRole = \DB::table('userModuleFeatures')
        // ->where('userAccountId', $user->id)
        // ->whereNotNull('roleId')
        // ->orderByDesc('id') // ALWAYS take latest
        // ->value('roleId');

        // Debug roleId
        Log::info('Role ID: ' . $userRole);

        if (!$userRole) {
            return response()->json(['error' => 'No role assigned'], 403);
        }

        $features = ModuleFeatures::with('module')
            ->join('roleModuleFeatures as rmf', 'rmf.moduleFeatureId', '=', 'moduleFeatures.id')
            ->where('rmf.roleId', $userRole)
            ->where('rmf.moduleFeatureEnabled', 'Yes')
            ->select('moduleFeatures.*')
            ->distinct()
            ->get();

        // Debug features count
        \Log::info('Features count: ' . $features->count());

        $menu = [];

        foreach ($features as $feature) {
            if (!$feature->module) {
                continue;
            }

            $moduleKey  = $feature->module->id;
            $moduleName = $feature->module->moduleName;

            if (!isset($menu[$moduleKey])) {
                $menu[$moduleKey] = [
                    'label'    => $moduleName,
                    'icon'     => 'folder',
                    'to'       => '/dashboard/' . strtolower(str_replace(' ', '-', $moduleName)),
                    'children' => []
                ];
            }

            $menu[$moduleKey]['children'][] = [
                'label' => $feature->featureName,
                'to'    => '/dashboard/' . strtolower(str_replace(' ', '-', $feature->featureName)),
                'icon'  => 'circle'
            ];
        }

        return response()->json(array_values($menu));

    } catch (\Exception $e) {
        \Log::error('Menu Error: ' . $e->getMessage());
        \Log::error('Line: ' . $e->getLine());
        \Log::error('File: ' . $e->getFile());
        return response()->json([
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
            'file'  => $e->getFile()
        ], 500);
    }
}
}