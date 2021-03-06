<x-jet-action-section>
    <x-slot name="title">
        Интеграции
    </x-slot>

    <x-slot name="description">
        <!-- Описание -->
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            Доступные интеграции
        </div>

        <div class="flex items-center mt-5">
            <a class="inline-flex items-center px-4 py-2 mr-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" href="{{ route('login.google.callback') }}">
                Google
            </a>

            <a class="inline-flex items-center px-4 py-2 mr-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" href="{{ route('login.asana.callback') }}">
                Asana
            </a>

            <a class="inline-flex items-center px-4 py-2 mr-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" href="{{ route('login.amocrm.callback') }}">
                amoCRM
            </a>
            <a class="inline-flex items-center px-4 py-2 mr-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" href="{{ route('login.yandex.callback') }}">
                Яндекс
            </a>
        </div>
    </x-slot>
</x-jet-action-section>