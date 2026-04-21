<?php

namespace App\Services;

use App\Models\UserAccountsModel;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function authenticate($username, $password)
    {
        $username = strtolower(explode('@', $username)[0]);

        if 
        (
            !$this->ldapAuthenticate($username, $password) &&
            !$this->pgsqlAuthenticate($username, $password)
        ) 
        {
            return null;
        }

        $user = UserAccountsModel::whereRaw('LOWER(username) = ?', [$username])->first();

        if (!$user) 
        {
            return null;
        }

        if ($user->status === 'Disabled') 
        {
            throw new \Exception('Account disabled', 403);
        }

        if ($user->activeUntil && now()->gt($user->activeUntil)) 
        {
            throw new \Exception('Account expired', 403);
        }

        return $user;
    }

    private function pgsqlAuthenticate($username, $password)
    {
        $user = UserAccountsModel::whereRaw('LOWER(username) = ?', [$username])->first();

        if (!$user) 
        {
            return false;
        }

        if (Hash::check($password, $user->password)) 
        {
            return true;
        }

        // Optional: Upgrade old password hash to new hashing method
        // if ($user->password === sha1($password)) { 
        //     $user->password = Hash::make($password);
        //     $user->save();

        //     return true;
        // }

        return false;
    }

    private function ldapAuthenticate($username, $password)
    {
        $ldapHost = "ldap://10.10.0.10";
        $ldapPort = 389;

        $connection = @ldap_connect($ldapHost, $ldapPort);

        if (!$connection) 
        {
            return false;
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        $dn = "cn={$username},cn=Users,dc=shc,dc=edu,dc=bz,dc=local";

        try 
        {
            return @ldap_bind($connection, $dn, $password);
        } 
        catch (\Exception $e) 
        {
            return false;
        }
    }
}