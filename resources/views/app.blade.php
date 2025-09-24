<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        {{-- <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@2.1.6/dist/vue-multiselect.min.css"> --}}
        <link rel="stylesheet" href="{{ secure_asset('css/vue-multiselect.min.css') }}">
        {{-- <script src="https://unpkg.com/vue-multiselect@2.1.6"></script>
        <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@2.1.6/dist/vue-multiselect.min.css"> --}}
        <!-- Scripts -->
        @routes
        <script>
            Ziggy.url = '{{ env('APP_URL') }}'
        </script>
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>