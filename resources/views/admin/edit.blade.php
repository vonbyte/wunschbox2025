<x-admin-layout :title="__('Edit wish')">
    <div class="card mx-auto max-w-2xl p-6 md:p-8">
        <h1 class="mb-6 font-hand text-4xl font-bold leading-none text-berry">{{ __('Edit wish') }}</h1>
        <x-wish-form
            :action="route('admin.wishes.update', $wish)"
            method="PATCH"
            :wish="$wish"
            variant="admin"
            {{-- Wishes on the wish note always stay the owner's --}}
            :show-receiver="!$wish->isOnWishNote()"
            :submit-text="__('Save')"
            :cancel-url="route('admin.index')"
        />
    </div>
</x-admin-layout>
