<x-admin-layout title="Profil" class="space-y-8">
    <!-- Profile Information -->
    <div class="card mx-auto max-w-2xl p-6 md:p-8">
        <h2 class="mb-6 font-hand text-4xl font-bold leading-none text-berry">
            Name und E-Mail
        </h2>

        @if(session('status') === 'profile-updated')
            <div class="flash" role="status">
                Profil gespeichert!
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf @method('PATCH')

            <div>
                <label for="name" class="mb-1 block text-sm font-bold text-ink">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="field-box">
                @error('name')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-bold text-ink">E-Mail *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                       class="field-box">
                @error('email')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="btn btn-primary">
                Speichern
            </button>
        </form>
    </div>

    <!-- Password -->
    <div class="card mx-auto max-w-2xl p-6 md:p-8">
        <h2 class="mb-6 font-hand text-4xl font-bold leading-none text-berry">
            Passwort ändern
        </h2>

        @if(session('status') === 'password-updated')
            <div class="flash" role="status">
                Passwort geändert!
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <label for="current_password" class="mb-1 block text-sm font-bold text-ink">Aktuelles Passwort *</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                       class="field-box">
                @error('current_password', 'updatePassword')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-bold text-ink">Neues Passwort *</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       class="field-box">
                @error('password', 'updatePassword')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-bold text-ink">Neues Passwort bestätigen *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="field-box">
            </div>

            <button type="submit"
                    class="btn btn-primary">
                Passwort ändern
            </button>
        </form>
    </div>
</x-admin-layout>
