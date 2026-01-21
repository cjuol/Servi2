<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TPV - Servi2' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS desde CDN (temporal hasta compilar assets) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- Estilos personalizados para scrollbar -->
    <style>
        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgb(243 244 246);
        }

        .dark ::-webkit-scrollbar-track {
            background: rgb(17 24 39);
        }

        ::-webkit-scrollbar-thumb {
            background: rgb(209 213 219);
            border-radius: 4px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: rgb(55 65 81);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgb(156 163 175);
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgb(75 85 99);
        }

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(209 213 219) rgb(243 244 246);
        }

        .dark * {
            scrollbar-color: rgb(55 65 81) rgb(17 24 39);
        }
    </style>

    <!-- Script para detectar y aplicar el tema -->
    <script>
        // Sincronizar con el tema de Filament
        if (localStorage.getItem('theme') === 'dark' || 
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="antialiased h-full">
    <!-- Layout de pantalla completa sin scroll principal -->
    <div class="h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        {{ $slot }}
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
