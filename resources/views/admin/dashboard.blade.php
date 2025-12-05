<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="bg-gray-800 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">🎄 Wishbox Admin</h1>
            <p class="text-gray-400 text-sm">Hallo, {{ auth()->user()->name }}!</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
                Abmelden
            </button>
        </form>
    </div>
</header>

<div class="container mx-auto px-4 py-8">

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif


    <!-- Guest Wishes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            📝 Gäste-Wünsche ({{ $guestWishes->count() }})
        </h2>

        @if($guestWishes->isEmpty())
            <p class="text-gray-500 py-4">Noch keine Wünsche von Gästen...</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-16">#</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-24">Bild</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700">Titel</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-32">Von</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-32">Status</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 w-64">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($guestWishes as $wish)
                        <tr class="hover:bg-gray-50">
                            <!-- Sort Nr -->
                            <td class="px-3 py-3">
                                <form method="POST" action="{{ route('admin.wishes.update-sort', $wish) }}"
                                      class="flex items-center">
                                    @csrf @method('PATCH')
                                    <input
                                        type="text"
                                        name="sortnr"
                                        value="{{ $wish->sortnr }}"
                                        class="w-14 px-2 py-1 text-sm border border-gray-300 rounded"
                                        onchange="this.form.submit()">
                                </form>
                            </td>

                            <!-- Image -->
                            <td class="px-3 py-3">
                                @if($wish->image)
                                    <img src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}"
                                         alt="{{ $wish->title }}"
                                         class="w-16 h-16 object-cover rounded">
                                @else
                                    <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2L9 8h2l-2 5h2l-3 7h8l-3-7h2l-2-5h2l-3-6z"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- Title -->
                            <td class="px-3 py-3">
                                <a href="{{ route('wish.show', $wish) }}"
                                   class="font-semibold text-gray-900 hover:text-christmas-red">
                                    {{ $wish->title }}
                                </a>
                                @if($wish->description)
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ Str::limit($wish->description, 50) }}</p>
                                @endif
                            </td>

                            <!-- Receiver -->
                            <td class="px-3 py-3 text-sm">{{ $wish->receiver }}</td>

                            <!-- Status -->
                            <td class="px-3 py-3">
                                <form method="POST" action="{{ route('admin.wishes.update-status', $wish) }}">
                                    @csrf @method('PATCH')
                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        class="text-xs px-2 py-1 rounded border-0 font-medium
                                                           {{ $wish->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                                                           {{ $wish->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                           {{ $wish->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                           {{ $wish->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}">
                                        @foreach(\App\Models\Wish::getStatuses() as $value => $label)
                                            <option
                                                value="{{ $value }}" {{ $wish->status === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="px-3 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.wishes.edit', $wish) }}"
                                       class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Bearbeiten
                                    </a>
                                    <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}"
                                          class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="text-xs text-green-600 hover:text-green-800 font-medium">
                                            → Meine Liste
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}"
                                          class="inline" onsubmit="return confirm('Wirklich löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-600 hover:text-red-800 font-medium">
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @endif
    </div>

    <!-- Add My Wish -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            ➕ Meinen Wunsch hinzufügen
        </h2>
        <x-wish-form
            :action="route('admin.wishes.store')"
            :show-receiver="true"
            submit-text="Hinzufügen"
            class="max-w-2xl"
        />
    </div>

    <!-- My Public Wishes -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ open: false }">
        <div class="p-6">
            <button @click="open = !open" class="w-full flex items-center justify-between text-left">
                <h3 class="text-2xl font-bold text-gray-800">
                    ⭐ Meine öffentliche Wunschliste ({{ $myWishes->count() }})
                </h3>
                <svg class="w-6 h-6 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="mt-6">
                @if($myWishes->isEmpty())
                    <p class="text-gray-500 py-4">Noch keine Wünsche auf deiner öffentlichen Liste...</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-16">#</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 w-24">Bild</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700">Titel</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 w-48">Aktionen</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($myWishes as $wish)
                                <tr class="hover:bg-gray-50">
                                    <!-- Sort Nr -->
                                    <td class="px-3 py-3">
                                        <form method="POST" action="{{ route('admin.wishes.update-sort', $wish) }}"
                                              class="flex items-center">
                                            @csrf @method('PATCH')
                                            <input
                                                type="number"
                                                name="sort_nr"
                                                value="{{ $wish->sort_nr }}"
                                                class="w-14 px-2 py-1 text-sm border border-gray-300 rounded"
                                                min="0"
                                                onchange="this.form.submit()">
                                        </form>
                                    </td>

                                    <!-- Image -->
                                    <td class="px-3 py-3">
                                        @if($wish->image)
                                            <img src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}"
                                                 alt="{{ $wish->title }}"
                                                 class="w-16 h-16 object-cover rounded">
                                        @else
                                            <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24"
                                                     fill="currentColor">
                                                    <path d="M12 2L9 8h2l-2 5h2l-3 7h8l-3-7h2l-2-5h2l-3-6z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Title -->
                                    <td class="px-3 py-3">
                                        <a href="{{ route('wish.show', $wish) }}"
                                           class="font-semibold text-gray-900 hover:text-christmas-red">
                                            {{ $wish->title }}
                                        </a>
                                        @if($wish->description)
                                            <p class="text-sm text-gray-500 line-clamp-1">{{ Str::limit($wish->description, 60) }}</p>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-3 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.wishes.edit', $wish) }}"
                                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                Bearbeiten
                                            </a>
                                            <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}"
                                                  class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="text-xs text-orange-600 hover:text-orange-800 font-medium">
                                                    Privat
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}"
                                                  class="inline" onsubmit="return confirm('Wirklich löschen?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-600 hover:text-red-800 font-medium">
                                                    Löschen
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</body>
</html>
