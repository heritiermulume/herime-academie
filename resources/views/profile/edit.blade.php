<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color: #003366;">
            <i class="fas fa-user-circle me-2"></i>{{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12" style="background: linear-gradient(135deg, #f7f9fa 0%, #e9ecef 100%);">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow-lg sm:rounded-lg" style="border-radius: 15px; border-left: 4px solid #ffcc33;">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-lg sm:rounded-lg" style="border-radius: 15px; border-left: 4px solid #ffcc33;">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-lg sm:rounded-lg" style="border-radius: 15px; border-left: 4px solid #ffcc33;">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
