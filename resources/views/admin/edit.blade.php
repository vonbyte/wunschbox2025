<x-admin-layout title="Wunsch bearbeiten">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <x-wish-form
            :action="route('admin.wishes.update', $wish)"
            method="PATCH"
            :wish="$wish"
            submit-text="Speichern"
            :cancel-url="route('admin.index')"
        />
    </div>
</x-admin-layout>
