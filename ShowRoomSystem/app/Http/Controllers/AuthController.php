<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // 👈 import Hash facade

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid credentials');
        }

        session([
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_created_at' => $user->created_at->format('M d, Y'),
        ]);

        return redirect('/dashboard')->with('success', 'Logged in successfully!');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}