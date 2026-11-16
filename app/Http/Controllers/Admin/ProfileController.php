<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\{Rule, Rules\Password};
class ProfileController extends Controller
{
    public function edit() { return view('admin.profile'); }
    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)], 'current_password' => ['required', 'current_password'], 'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()]]);
        $request->user()->fill(collect($data)->only(['name', 'email'])->all());
        if (! empty($data['password'])) { $request->user()->password = Hash::make($data['password']); }
        $request->user()->save(); $request->session()->regenerate(); return back()->with('success', 'Profile updated.');
    }
}
