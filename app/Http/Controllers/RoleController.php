<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\Module;
use App\Models\ModuleFeatures;
use App\Models\RoleModuleFeatures;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // GET /api/roles
    public function index()
    {
        $roles = Roles::select('roles.id', 'roles.rolename', 'roles.roledescription', 'roles.status')
            ->leftJoin('userModuleFeatures as umf', 'umf.roleId', '=', 'roles.id')
            ->selectRaw('COUNT(DISTINCT umf."userAccountId") as "userCount"')
            ->groupBy('roles.id', 'roles.rolename', 'roles.roledescription', 'roles.status')
            ->get();

        return response()->json($roles);
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $request->validate([
            'rolename'        => 'required|string|unique:roles,rolename',
            'roledescription' => 'nullable|string',
            'status'          => 'sometimes|in:Active,Inactive'
        ]);

        DB::beginTransaction();

        try {
            $role = Roles::create([
                'rolename'        => $request->rolename,
                'roledescription' => $request->roledescription ?? null,
                'status'          => $request->status ?? 'Active',
            ]);

            // assign ALL features as disabled
            $features = ModuleFeatures::all();

            foreach ($features as $feature) {
                RoleModuleFeatures::create([
                    'roleId'               => $role->id,
                    'moduleFeatureId'      => $feature->id,
                    'moduleFeatureEnabled' => 'No'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Role created successfully',
                'role'    => $role
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT /api/roles/{id}
    public function update(Request $request, $id)
    {
        $role = Roles::findOrFail($id);

        $request->validate([
            'rolename'        => 'sometimes|string|unique:roles,rolename,' . $id,
            'roledescription' => 'nullable|string',
            'status'          => 'sometimes|in:Active,Inactive'
        ]);

        $role->update($request->only([
            'rolename',
            'roledescription',
            'status'
        ]));

        return response()->json([
            'message' => 'Role updated successfully',
            'role'    => $role
        ]);
    }

    // DELETE /api/roles/{id}
    public function destroy($id)
    {
        try {
            $role = Roles::findOrFail($id);

            // prevent deleting if users assigned
            if ($role->userModuleFeatures()->exists()) {
                return response()->json([
                    'error' => 'Cannot delete role. Users are still assigned to it.'
                ], 422);
            }

            // delete permissions first
            $role->roleModuleFeatures()->delete();

            // delete role
            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/role-permissions/{roleId}
 public function getPermissions($roleId)
{
    $modules = Module::select('id', 'moduleName')
        ->with(['features' => function ($query) use ($roleId) {

            $query->leftJoin('roleModuleFeatures as rmf', function ($join) use ($roleId) {
                $join->on('rmf.moduleFeatureId', '=', 'moduleFeatures.id')
                     ->where('rmf.roleId', '=', $roleId);
            })
            ->select(
                'moduleFeatures.id',
                'moduleFeatures.featureName',
                'moduleFeatures.featureDescription',
                'moduleFeatures.moduleId',
                DB::raw('COALESCE(rmf."moduleFeatureEnabled", \'No\') as "moduleFeatureEnabled"')
            );

            \Log::info('Final SQL', [
                'sql'      => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }])
        ->get();


    return response()->json($modules);
}

    // PUT /api/role-permissions/{roleId}
    public function updatePermissions(Request $request, $roleId)
    {
        $request->validate([
            'permissions'                        => 'required|array',
            'permissions.*.moduleFeatureId'      => 'required|integer',
            'permissions.*.moduleFeatureEnabled' => 'required|in:Yes,No'
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->permissions as $permission) {
                RoleModuleFeatures::updateOrCreate(
                    [
                        'roleId'          => $roleId,
                        'moduleFeatureId' => $permission['moduleFeatureId']
                    ],
                    [
                        'moduleFeatureEnabled' => $permission['moduleFeatureEnabled']
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Permissions updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}