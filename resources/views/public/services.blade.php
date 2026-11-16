@extends('layouts.public')
@section('title','Our Services | PropertyME Bali')
@section('description','Explore PropertyME services: land sourcing, villa design and development, real estate listing, buying, renting and villa management in Bali.')
@section('content')
<section class="section-space"><div class="container-wide"><x-breadcrumb :items="['Our Services'=>null]"/><div class="grid gap-8 lg:grid-cols-2"><div><p class="eyebrow text-muted">{{ __('An end-to-end perspective') }}</p><h1 class="section-title">{{ __('One Partner.') }}<br><em class="text-gold">{{ __('Every Possibility.') }}</em></h1></div><p class="body-copy max-w-md lg:self-end">{{ __('Your ambitions set the direction. Our connected range of services helps you navigate every stage of property ownership in Bali, from a first idea to everyday management.') }}</p></div><div class="mt-14 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">@forelse($services as $service)<x-service-card :service="$service" :index="$loop->index"/>@empty<x-empty-state class="sm:col-span-2 xl:col-span-4" title="Let's discuss your property plans."/>@endforelse</div></div></section><x-final-cta/>
@endsection
