<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(UserRequest $request, UserService $userService)
    {
        $filters = $request->filters();
        $users = $userService->datatable($filters);
        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render('Users/Index', compact('users', 'filters'));
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', ['user' => $user->load('role')]);
    }

    public function create()
    {
        return Inertia::render('Users/Form', ['roles' => Role::select('id', 'nama')->get(), 'mode' => 'create']);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Form', ['user' => $user, 'roles' => Role::select('id', 'nama')->get(), 'mode' => 'edit']);
    }

    public function store(UserRequest $request, UserService $userService)
    {
        $userService->create($request->validated());

        return redirect()->route('users.index');
    }

    public function update(UserRequest $request, User $user, UserService $userService)
    {
        $userService->update($user, $request->validated());

        return redirect()->route('users.index');
    }

    public function destroy(User $user, UserService $userService)
    {
        $userService->delete($user);

        return redirect()->route('users.index');
    }

    public function resetPassword(User $user, UserService $userService)
    {
        $userService->resetPassword($user);

        return redirect()->route('users.index');
    }
}
