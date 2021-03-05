<x-app-layout>


    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Календари
        </h2>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    @foreach($users as $user)
                        <div class="mb-3 text-gray-500">
                            <x-jet-label for="{{ $user->email }}" value="{{ $user->name }}" />
                            <x-jet-input id="{{ $user->email }}" type="text" class="mt-1 block w-full" value="{{ $user->google_calendar_id }}" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
</x-app-layout>
