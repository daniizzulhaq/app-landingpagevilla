<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Password, RateLimiter};
use Illuminate\Support\Str;
use Illuminate\Validation\{Rules\Password as PasswordRule, ValidationException};
class AuthController extends Controller
{
    public function create() { return view('auth.login'); }
    public function store(LoginRequest $request)
    {
        $identifier = Str::lower(trim($request->string('email')->toString()));
        $local = app()->environment(['local', 'testing']);
        $email = $local && $identifier === 'admin' ? 'admin@propertyme.test' : $identifier;
        $key = 'login:'.$email.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) { throw ValidationException::withMessages(['email' => 'Too many attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.']); }
        RateLimiter::hit($key, 60);
        $credentials = ['email' => $email, 'password' => $request->validated('password')];
        if (! $local) { $credentials['is_demo'] = false; }
        if (! Auth::attempt($credentials, false)) { throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']); }
        RateLimiter::clear($key); $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }
    public function destroy(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('admin.login'); }
    public function forgot() { abort_unless(config('propertyme.password_reset_enabled'), 404); return view('auth.password', ['reset' => false]); }
    public function email(Request $request)
    {
        abort_unless(config('propertyme.password_reset_enabled'), 404); $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($request->only('email'));
        return back()->with('success', 'If an admin account matches this email, a reset link will be sent.');
    }
    public function reset(Request $request, string $token) { abort_unless(config('propertyme.password_reset_enabled'), 404); return view('auth.password', ['reset' => true, 'token' => $token, 'email' => $request->query('email')]); }
    public function updatePassword(Request $request)
    {
        abort_unless(config('propertyme.password_reset_enabled'), 404);
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()]]);
        $status = Password::reset($data, function (User $user, string $password) { $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save(); event(new \Illuminate\Auth\Events\PasswordReset($user)); });
        if ($status !== Password::PASSWORD_RESET) { return back()->withErrors(['email' => __($status)]); }
        return redirect()->route('admin.login')->with('success', 'Password reset. Please sign in.');
    }
}
