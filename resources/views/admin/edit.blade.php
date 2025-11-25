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
        <x-wish-form
            :action="route('admin.wishes.update', $wish)"
            method="PATCH"
            :wish="$wish"
            submit-text="Speichern"
            :cancel-url="route('admin.index')"
        />
    </div>
</div>
</body>
</html>
