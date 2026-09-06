<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Suppress external extension / DevTools performance observer error --}}
        <script>
            (function() {
                function isExtensionNoise(val) {
                    if (!val) return false;
                    var str = typeof val === 'object' ? (val.message || val.stack || '') : String(val);
                    return str.indexOf('startTime') !== -1 ||
                           str.indexOf('reportAllChanges') !== -1 ||
                           str.indexOf('chrome-extension://') !== -1 ||
                           str.indexOf('moz-extension://') !== -1;
                }

                window.addEventListener('error', function(e) {
                    if (isExtensionNoise(e.message) || isExtensionNoise(e.error) || (e.filename && isExtensionNoise(e.filename))) {
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        return true;
                    }
                }, true);

                window.addEventListener('unhandledrejection', function(e) {
                    if (isExtensionNoise(e.reason)) {
                        e.stopImmediatePropagation();
                        e.preventDefault();
                    }
                }, true);

                var origError = console.error;
                console.error = function() {
                    for (var i = 0; i < arguments.length; i++) {
                        if (isExtensionNoise(arguments[i])) {
                            return;
                        }
                    }
                    origError.apply(console, arguments);
                };
            })();
        </script>

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
