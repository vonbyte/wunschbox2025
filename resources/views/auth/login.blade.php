<x-guest-layout>
    <h1 class="mb-6 font-hand text-4xl font-bold leading-none text-berry">Anmelden</h1>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-Mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Passwort" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-line accent-pine" name="remember">
                <span class="ms-2 text-sm text-ink-muted">Angemeldet bleiben</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-pine underline underline-offset-4 hover:text-pine-dark" href="{{ route('password.request') }}">
                    Passwort vergessen?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Anmelden
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
