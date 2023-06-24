<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>PIM</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- <title>{{ config('app.name', 'PIM') }}</title> -->

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
    </head>
    <body>
        <div id="app">
            <v-app>
                <v-container fluid pa-0>
                    <main-toolbar :user="{{ json_encode(Auth::user()) }}"></main-toolbar>
                    <v-progress-linear color="grey-lighten-1" indeterminate class='ma-0' style='position:fixed;top:64px;z-index:10' v-if="false"></v-progress-linear>
                    <v-main>
                        <router-view></router-view>
                        <!-- <router-view v-slot="{ Component }">
                            <transition name="slide-fade">
                                <component :is="Component" />
                            </transition>
                        </router-view> -->
                    </v-main>
                    <div style="position:fixed;top:75px;left:12.5vw">
                        <alert-center></alert-center>
                    </div>
                </v-container>
            </v-app>
        </div>
    </body>
</html>

