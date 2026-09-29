{{--
    Shared <head> contents for every layout in resources/views/layouts.

    Each layout used to repeat its own copy of this: CDN Bootstrap CSS+JS,
    an unversioned unpkg lucide@latest script, a 1.3 MB PNG as the favicon,
    and hundreds of lines of inline CSS re-sent in every HTML response.
    Bootstrap and Lucide are now bundled by Vite (resources/js/app.js), and
    each layout's own CSS lives in resources/css/layouts/*.css — hashed,
    long-cached files instead of per-request payload.

    $layoutCss: the calling layout's stylesheet, or null.
--}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@hasSection('title')@yield('title') · @endif CSU Aparri Research Repository</title>

{{-- After a form redirects back to the same page (approve, reject,
     feedback), keep the reader where they were instead of jumping to the
     top. --}}
<meta name="turbo-refresh-scroll" content="preserve">

<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

{{-- The two icon fonts still come from CDNs; opening those connections
     early takes a round trip off the first paint. --}}
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@vite(array_values(array_filter(['resources/css/layout.css', $layoutCss ?? null, 'resources/js/app.js'])))
