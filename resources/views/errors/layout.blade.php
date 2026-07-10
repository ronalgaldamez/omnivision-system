<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} - {{ $title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-8 max-w-sm w-full text-center">
        {{-- Ilustración DiceBear + icono superpuesto --}}
        <div class="relative inline-block mb-6">
            <img src="https://api.dicebear.com/9.x/{{ $dicebearStyle }}/svg?seed={{ $dicebearSeed }}&size=160&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf"
                alt="{{ $title }}" class="w-36 h-36 mx-auto rounded-full">
            <span class="material-symbols-outlined absolute -bottom-1 -right-1 text-4xl bg-white rounded-full p-1 shadow-sm text-gray-600">{{ $icon }}</span>
        </div>

        {{-- Código de error --}}
        <div class="text-6xl font-bold text-gray-200 mb-2">{{ $code }}</div>

        {{-- Título --}}
        <h1 class="text-xl font-semibold text-gray-800 mb-2">{{ $title }}</h1>

        {{-- Descripción --}}
        <p class="text-sm text-gray-500 mb-6 leading-relaxed">{{ $description }}</p>

        {{-- Botón --}}
        <div class="flex flex-col gap-2">
            <a href="{{ auth()->check() ? url('/dashboard') : url('/') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                <span class="material-symbols-outlined text-base">home</span>
                Ir al inicio
            </a>
            @guest
            <a href="{{ url('/login') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                <span class="material-symbols-outlined text-base">login</span>
                Iniciar sesión
            </a>
            @endguest
        </div>
    </div>
</body>
</html>
