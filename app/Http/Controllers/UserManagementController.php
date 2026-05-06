<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalInfo;
use App\Models\UserAccountsModel;
use App\Models\UserModuleFeatures;
use App\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonalInfo::select('id', 'firstName', 'lastName')
            ->with(['userAccount.userModuleFeatures.role'])
            ->whereHas('userAccount');

        /*
        search bar base on fname and lname
        */
        $search = trim($request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {

                $q->where('firstName', 'ilike', "%{$search}%")
                  ->orWhere('lastName', 'ilike', "%{$search}%")
                  ->orWhereHas('userAccount', function ($q2) use ($search) {
                      $q2->where('username', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('userAccount.userModuleFeatures.role', function ($q3) use ($search) {
                      $q3->where('rolename', 'ilike', "%{$search}%");
                  });

            });
        }

        /*
         role filter based on the role name*/
      if ($request->role) {
    $query->whereHas('userAccount.userModuleFeatures.role', function ($q) use ($request) {
        $q->where('id', $request->role); 
    });
}

        /*
        status filter based on the user account status, active or inactive
        */
        if ($request->status) {

            $query->whereHas('userAccount', function ($q) use ($request) {

                $q->where('status', $request->status === 'active'
                    ? 'Enabled'
                    : 'Disabled'
                );

            });

        }

        $users = $query->get()->map(function ($user) {

            $account = $user->userAccount;

            $roleName = $account?->userModuleFeatures?->first()?->role?->rolename;

            return [
                'id' => $user->id,

                'userAccountId' => $account?->id,

                'fullName' => trim($user->firstName . ' ' . $user->lastName),
                'username' => $account?->username,
                'roleName' => $roleName,

                'status' => match (strtolower($account?->status)) {
                    'enabled' => 'active',
                    'disabled' => 'inactive',
                    default => null,
                },
            ];
        });

        return response()->json($users);
    }

    /*
   
     //updates status to enabled or disabled based on the input of active or inactive respectively
    */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $account = UserAccountsModel::where('id', $id)->first();

        if (!$account) {
            return response()->json(['message' => 'User account not found'], 404);
        }

        $account->status = $request->status === 'active'
            ? 'Enabled'
            : 'Disabled';

        $account->save();

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }

   // updates the role of the user based on the input of the roleId
public function updateRole($id, Request $request)
{
    $request->validate([
        'roleId' => 'required|integer'
    ]);

    $updated = UserModuleFeatures::where('userAccountId', $id)
        ->update([
            'roleId' => $request->roleId
        ]);

    if ($updated === 0) {
        return response()->json([
            'message' => 'No role records found for this user'
        ], 404);
    }

    return response()->json([
        'message' => 'All roles updated successfully',
        'updated_rows' => $updated
    ]);
}
}