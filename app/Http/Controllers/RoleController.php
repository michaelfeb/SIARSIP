<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    //

    public function getRoles()
    {
        $roles = Role::select('id', 'nama')->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }
}
