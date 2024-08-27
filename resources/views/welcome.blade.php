<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            /* @import url('https://fonts.googleapis.com/css2?family=Shrikhand&display=swap'); */
            @import url('https://fonts.googleapis.com/css2?family=Bubblegum+Sans&display=swap');
            /* @import url('https://fonts.googleapis.com/css2?family=Sue+Ellen+Francisco&display=swap'); */
            @import url('https://fonts.googleapis.com/css2?family=Handlee&display=swap');
            .pimTitleFont {
                /* font-family: "Shrikhand", serif; */
                font-family: "Bubblegum Sans", sans-serif;
                font-weight: 400;
                font-style: normal;
            }
            .pimSubtitleFont {
                /* font-family: "Sue Ellen Francisco", cursive; */
                font-family: "Handlee", cursive;
                font-weight: 400;
                font-style: normal;
            }
        </style>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PIM') }}</title>
        <link rel="shortcut icon" href="{{ asset('images/pimfavicon.ico') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- <style>
            .slide-fade-enter-active {
                transition: all .2s ease;
            }
            .slide-fade-leave-active {
                transition: all .2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
            }
            .slide-fade-leave-to{
                transform: translateX(40px);
                opacity: 0;
                position: absolute;
            }
            .slide-fade-enter-from{
                transform: translateX(-40px);
                opacity: 0;
            }
        </style> -->
        <script>
            window.Laravel = { env: '{{ app()->environment() }}' };
        </script>
    </head>
    <body>
        <div id="app">
            <v-app>
                <v-container fluid pa-0>
                    <main-toolbar :user="{{ json_encode(Auth::user()) }}"></main-toolbar>
                    <loading-indicator style='position:fixed;top:64px;z-index:10'></loading-indicator>
                    <v-main>
                        <router-view></router-view>
                        <!-- <router-view v-slot="{ Component }">
                            <transition name="slide-fade">
                                <component :is="Component" />
                            </transition>
                        </router-view> -->
                    </v-main>
                    <div style="position:fixed;top:75px;left:12.5vw;z-index:9999">
                        <notification-center></notification-center>
                    </div>
                </v-container>
            </v-app>
        </div>
    </body>
</html>

