<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RoadShield RDC') }} - {{ ucfirst(Auth::user()->role ?? 'Système') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-vh-100">
        {{-- L'ERREUR EST PROBABLEMENT DANS CE FICHIER CI-DESSOUS --}}
        @include('layouts.navigation')

        @if (isset($header))
            <header class="container pt-4">
                <div class="glass-card px-4 py-4 px-lg-5 py-lg-4 mb-4">
                    <div class="mx-auto text-center" style="max-width: 48rem;">
                        {{ $header }}
                    </div>
                </div>
            </header>
        @endif

        <main class="container pb-5">
            @if (session('success'))
                <div class="mb-4 p-4 alert alert-success shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill fs-4 text-success me-3 flex-shrink-0"></i>
                        <div>
                            <h4 class="h6 fw-bold mb-1">Succès</h4> {{-- Correction orthographe --}}
                            <p class="mb-0">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 p-4 alert alert-warning shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-circle-fill fs-4 text-warning me-3 flex-shrink-0"></i>
                        <div>
                            <h4 class="h6 fw-bold mb-1">Attention</h4>
                            <p class="mb-0">{{ session('warning') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 alert alert-danger shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-3 flex-shrink-0"></i>
                        <div>
                            <h4 class="h6 fw-bold mb-1">Erreur</h4>
                            <p class="mb-0">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
