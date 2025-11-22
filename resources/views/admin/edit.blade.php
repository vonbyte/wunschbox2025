<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wunsch bearbeiten</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<header class="bg-gray-800 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4">
        <h1 class="text-2xl font-bold">🎄 Wunsch bearbeiten</h1>
    </div>
</header>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">

        <form method="POST" action="{{ route('admin.wishes.update', $wish) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Titel *
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $wish->title) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent">
                @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="receiver" class="block text-sm font-medium text-gray-700 mb-2">
                    Empfänger *
                </label>
                <input
                    type="text"
                    id="receiver"
                    name="receiver"
                    value="{{ old('receiver', $wish->receiver) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent">
                @error('receiver')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Beschreibung
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent">{{ old('description', $wish->description) }}</textarea>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                    Abbrechen
                </a>
                <button type="submit" class="flex-1 bg-christmas-red hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
