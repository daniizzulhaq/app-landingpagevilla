@extends('layouts.guest')
@section('title','Admin Login')
@section('content')
<p class="eyebrow text-muted">Welcome back</p><h2 class="mt-5 text-5xl text-navy">Sign in to your<br>workspace.</h2><p class="mb-9 mt-5 text-xs leading-6 text-muted">Enter your administrator credentials to continue.</p>
<form action="{{ route('admin.login.store') }}" method="POST" class="space-y-6">@csrf
    <div><label class="field-label" for="email">{{ app()->environment(['local','testing']) ? 'Email or username' : 'Email address' }}</label><input type="{{ app()->environment(['local','testing']) ? 'text' : 'email' }}" name="email" id="email" class="field" value="{{ old('email') }}" autocomplete="username" required autofocus maxlength="255"></div>
    <div><label class="field-label" for="password">Password</label><input type="password" name="password" id="password" class="field" autocomplete="current-password" required></div>
    <button type="submit" class="button button-navy w-full">Sign In<x-icon name="arrow-right" class="h-4 w-4"/></button>
</form>
@if(config('propertyme.password_reset_enabled'))<a href="{{ route('admin.password.request') }}" class="mt-5 inline-flex min-h-11 items-center text-xs text-muted underline">Forgot your password?</a>@endif
@endsection
