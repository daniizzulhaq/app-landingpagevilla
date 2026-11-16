<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

Artisan::command('propertyme:admin {email?} {--name=Administrator}', function () {
    $email = $this->argument('email') ?: $this->ask('Admin email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error('Enter a valid email.'); return 1; }
    if (User::where('email', $email)->exists()) { $this->error('Account already exists. Use the configured password reset or profile screen.'); return 1; }
    $password = $this->secret('Password (12+ characters, upper/lowercase, number, symbol)');
    $validator = validator(['password' => $password], ['password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()]]);
    if ($validator->fails()) { $this->error($validator->errors()->first()); return 1; }
    if ($password !== $this->secret('Confirm password')) { $this->error('Passwords do not match.'); return 1; }
    User::create(['name' => $this->option('name'), 'email' => $email, 'password' => Hash::make($password)]);
    $this->info('Admin account created.');
})->purpose('Securely provision an administrator without public registration');

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
