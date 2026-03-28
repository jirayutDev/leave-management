<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::orderBy('name')->paginate(20);
        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admins.index')->with('success', 'เพิ่ม admin เรียบร้อยแล้ว');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $admin->delete();
        return back()->with('success', 'ลบ admin เรียบร้อยแล้ว');
    }
}
