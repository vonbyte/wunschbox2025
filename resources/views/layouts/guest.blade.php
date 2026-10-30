<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen">
        <header class="bg-pine">
            <x-fairy-lights />
            <div class="px-6 pb-6 text-center">
                <a href="{{ route('wishbox') }}" class="inline-block -rotate-2 font-hand text-5xl font-bold leading-none text-paper">Wunschbox</a>
            </div>
        </header>
        <div class="twine"></div>

        <main class="px-4 py-10">
            <div class="card mx-auto w-full max-w-md p-6">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
