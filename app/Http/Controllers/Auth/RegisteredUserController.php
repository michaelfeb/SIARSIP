<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|digits_between:5,20|unique:users,nim',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'program_studi' => 'required',
            'nomor_telpon' => ['required', 'digits_between:10,12'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.numeric' => 'NIM harus berupa angka.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'nomor_telpon.required' => 'Nomor Telpon wajib diisi.',
            'nomor_telpon.digits_between' => 'Nomor telpon harus terdiri dari 10 sampai 12 digit.'
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'email' => $request->email,
            'program_studi' => $request->program_studi,
            'password' => Hash::make($request->password),
            'role_id' => 1, // role mahasiswa
        ]);

        event(new Registered($user));

        return to_route('login');
    }
}
