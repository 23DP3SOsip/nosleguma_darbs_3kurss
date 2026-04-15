@extends('layouts.app', ['title' => 'Pieslēgšanās'])

@section('content')
    <div class="mx-auto mt-10 max-w-md rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-bold">Pieslēgšanās</h1>
        <p class="mb-6 text-sm text-slate-600">Publiska reģistrācija šajā sistēmā nav pieejama.</p>

        <form method="POST" action="{{ route('pieslegties.izpilde') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-pasts</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Parole</label>
                <input id="password" name="password" type="password" required class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-md bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-800">
                Ieiet sistēmā
            </button>
        </form>
    </div>
@endsection
