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

    <!-- Add My Wish -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            ➕ Meinen Wunsch hinzufügen
        </h2>
        <form method="POST" action="{{ route('admin.wishes.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input
                    type="text"
                    name="title"
                    placeholder="Titel *"
                    required
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition">
                    Hinzufügen
                </button>
            </div>
            <textarea
                name="description"
                rows="2"
                placeholder="Beschreibung (optional)"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent"></textarea>
        </form>
    </div>

    <!-- My Public Wishes -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-christmas-red pb-2">
            ⭐ Meine öffentliche Wunschliste ({{ $myWishes->count() }})
        </h2>

        @if($myWishes->isEmpty())
            <p class="text-gray-500 py-4">Noch keine Wünsche auf deiner öffentlichen Liste...</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Titel</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Beschreibung</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($myWishes as $wish)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $wish->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ Str::limit($wish->description, 50) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.wishes.edit', $wish) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Bearbeiten
                                </a>
                                <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium">
                                        Privat
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}" class="inline" onsubmit="return confirm('Wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                        Löschen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

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
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Titel</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Von</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Beschreibung</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($guestWishes as $wish)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $wish->title }}</td>
                            <td class="px-4 py-3">{{ $wish->receiver }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ Str::limit($wish->description, 40) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.wishes.edit', $wish) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Bearbeiten
                                </a>
                                <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-green-600 hover:text-green-800 font-medium">
                                        → Öffentlich
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}" class="inline" onsubmit="return confirm('Wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                        Löschen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
</body>
</html>
