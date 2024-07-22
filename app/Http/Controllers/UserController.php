<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    function index()
    {
        $users = User::all(); // Mengambil semua data pengguna dari tabel users

        $users = DB::table('users')->toSql();// Menampilkan query SQL dan menghentikan eksekusi skrip setelahnya
        dd(($users));

        // return view('dashboard', [
        //     'users' => $users
        // ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
