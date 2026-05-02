<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        return Inertia::render('User/Index', [
            'users' => User::latest()->get()
        ]);
    }

    /**
     * Menampilkan form tambah akun baru.
     */
    public function create()
    {
        return Inertia::render('User/Create');
    }

    /**
     * Menyimpan data akun baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,operator',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('message', 'Akun pengguna berhasil dibuat!');
    }

    /**
     * Menghapus akun pengguna (opsional).
     */
    public function destroy(User $user)
    {
        // Cegah admin menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('message', 'Akun berhasil dihapus.');
    }
}