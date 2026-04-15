@extends('layouts.app', ['title' => 'Admin panelis'])

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl bg-white p-6 shadow-sm lg:col-span-1">
            <h1 class="text-xl font-bold">Jauna lietotāja izveide</h1>
            <p class="mt-1 text-sm text-slate-600">
                @if (auth()->user()->role === 'vadiba')
                    Jūs varat izveidot admin un user kontus.
                @elseif (auth()->user()->role === 'admin')
                    Jūs varat izveidot tikai user kontus.
                @endif
            </p>

            <form method="POST" action="{{ route('admin.lietotaji.izveidot') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium">Vārds</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium">E-pasts</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
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

                <div>
                    <label for="role" class="mb-1 block text-sm font-medium">Loma</label>
                    <select id="role" name="role" required class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-slate-500 focus:outline-none">
                        <option value="">Izvēlieties lomu</option>
                        @if (auth()->user()->role === 'vadiba')
                            <option value="admin" @selected(old('role') === 'admin')>admin</option>
                        @endif
                        <option value="user" @selected(old('role') === 'user')>user</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-800">
                    Izveidot lietotāju
                </button>
            </form>
        </section>

        <section class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-xl font-bold">Visi lietotāji</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2">ID</th>
                            <th class="px-3 py-2">Vārds</th>
                            <th class="px-3 py-2">E-pasts</th>
                            <th class="px-3 py-2">Loma</th>
                            <th class="px-3 py-2">Izveidoja</th>
                            <th class="px-3 py-2">Izveidots</th>
                            <th class="px-3 py-2">Darbības</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-slate-200">
                                <td class="px-3 py-2">{{ $user->id }}</td>
                                <td class="px-3 py-2">{{ $user->name }}</td>
                                <td class="px-3 py-2">{{ $user->email }}</td>
                                <td class="px-3 py-2">{{ $user->role }}</td>
                                <td class="px-3 py-2">{{ $user->createdBy?->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2">
                                    @php
                                        $authRole = auth()->user()->role;
                                        $canDelete = ($authRole === 'vadiba' && in_array($user->role, ['admin', 'user'], true))
                                            || ($authRole === 'admin' && $user->role === 'user');
                                    @endphp

                                    @if ($canDelete)
                                        <form method="POST" action="{{ route('admin.lietotaji.dzest', $user) }}" onsubmit="return confirm('Vai tiešām dzēst šo lietotāju?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">
                                                Dzēst
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400">Nav atļauts</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-500">Lietotāji nav atrasti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
