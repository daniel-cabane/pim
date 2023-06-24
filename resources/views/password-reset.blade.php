<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reset password</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- <title>{{ config('app.name', 'PIM') }}</title> -->

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app">
            <v-app>
                <v-container fluid pa-0>
                    <v-main style="height:100vh;width:100%;display:flex;justify-content:center;align-items:center;">
                        <reset-password-card token="{{ request()->token }}" email="{{ request()->email }}"></reset-password-card>
                    </v-main>
                </v-container>
            </v-app>
        </div>
    </body>
</html>

