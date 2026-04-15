<!DOCTYPE html>
<html lang="lv">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Lietotāju sistēma' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        @auth
            <header class="bg-white shadow-sm">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                    <nav class="flex items-center gap-4">
                        <a href="{{ route('sakums') }}" class="rounded-md px-3 py-2 text-sm font-semibold {{ request()->routeIs('sakums') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-200' }}">
                            Sākums
                        </a>
                        @if (in_array(auth()->user()->role, ['admin', 'vadiba'], true))
                            <a href="{{ route('admin.panelis') }}" class="rounded-md px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-200' }}">
                                Admin panelis
                            </a>
                        @endif
                    </nav>
                    <div class="flex items-center gap-3">
                        <div class="text-right text-sm">
                            <p class="font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-slate-500">Loma: {{ auth()->user()->role }}</p>
                        </div>
                        <form method="POST" action="{{ route('iziet') }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Iziet
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('statuss'))
                <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('statuss') }}
                </div>
            @endif

            @if (session('kluda'))
                <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('kluda') }}
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>
