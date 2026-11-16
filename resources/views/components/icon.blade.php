@props(['name' => 'arrow-up-right'])
<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('message')<path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H7l-5 3V11.5A8.5 8.5 0 0 1 10.5 3h2a8.5 8.5 0 0 1 8.5 8.5Z"/><path d="M7 9h9M7 13h6"/>@break
@case('heart')<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>@break
@case('share')<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-4m-6.8 7 6.8 4"/>@break
@case('arrow-right')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
@case('arrow-down')<path d="M12 4v16m-6-6 6 6 6-6"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('close')<path d="m6 6 12 12M6 18 18 6"/>@break
@case('pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
@case('bed')<path d="M3 18v-7h18v7M3 15h18M5 11V6h14v5M8 11V8h4v3m0-3h4v3"/>@break
@case('bath')<path d="M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5v-3Zm2 0V5a2 2 0 0 1 4 0M7 20v2m10-2v2M7 6h4"/>@break
@case('area')<path d="M3 9V3h6m6 0h6v6m0 6v6h-6m-6 0H3v-6M3 3l7 7m11 11-7-7"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('building')<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-7h6v7M9 9h.01M15 9h.01"/>@break
@case('layers')<path d="m12 3 10 5-10 5L2 8l10-5Zm-10 9 10 5 10-5M2 16l10 5 10-5"/>@break
@case('key')<circle cx="8" cy="8" r="5"/><path d="m12 12 9 9m-5-5 3-3m-7-1 3-3"/>@break
@case('shield')<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6l9-4Z"/><path d="m8 12 3 3 5-6"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('mail')<rect x="3" y="5" width="18" height="14"/><path d="m3 5 9 8 9-8"/>@break
@case('plus')<path d="M12 4v16M4 12h16"/>@break
@case('search')<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>@break
@case('user')<circle cx="12" cy="7" r="4"/><path d="M4 21v-3a8 8 0 0 1 16 0v3"/>@break
@case('image')<rect x="3" y="3" width="18" height="18"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 4 4 3-3 5 5"/>@break
@case('whatsapp')<path d="M21 11.5a9 9 0 0 1-13.4 7.9L3 21l1.6-4.6A9 9 0 1 1 21 11.5Z"/><path d="M8 7c-2 2 2 7 6 9 1 .5 3-1 2-2l-2-1-1 1c-1.5-.5-3.5-2.5-4-4l1-1-1-2H8Z"/>@break
@default<path d="M6 18 18 6M6 6h12v12"/>
@endswitch
</svg>
