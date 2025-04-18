<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    //

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = \App\Models\User::with('role')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $users = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }


    public function create()
    {
        $roles = Role::select('id', 'nama')->get();

        return Inertia::render('Users/Form', [
            'roles' => $roles,
            'mode' => 'create',
            'user' => null,
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::select('id', 'nama')->get();

        return Inertia::render('Users/Form', [
            'user' => $user,
            'roles' => $roles,
            'mode' => 'edit',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email' . ($id ? ",$id" : '')],
            'role_id' => 'required|int',
        ];

        if (!$id) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($id) {
            $user = User::findOrFail($id);
            $user->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'role_id' => $request->role_id,
            ]);
            return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui');
        } else {
            User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'password' => bcrypt($request->password),
            ]);
            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
