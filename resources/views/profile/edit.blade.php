<x-admin-layout title="Profil" class="space-y-8">
    <x-slot:actions>
        <a href="{{ route('admin.index') }}" class="text-gray-300 hover:text-white transition">
            ← Zurück zum Admin
        </a>
    </x-slot:actions>

    <!-- Profile Information -->
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            Name und E-Mail
        </h2>

        @if(session('status') === 'profile-updated')
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                Profil gespeichert!
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-Mail *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="bg-christmas-red hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                Speichern
            </button>
        </form>
    </div>

    <!-- Password -->
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            Passwort ändern
        </h2>

        @if(session('status') === 'password-updated')
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                Passwort geändert!
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Aktuelles Passwort *</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                @error('current_password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Neues Passwort *</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                @error('password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Neues Passwort bestätigen *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
            </div>

            <button type="submit"
                    class="bg-christmas-red hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                Passwort ändern
            </button>
        </form>
    </div>
</x-admin-layout>
