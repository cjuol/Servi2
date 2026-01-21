<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <x-filament::avatar.user
                size="lg"
                class="!bg-cyan-500/10"
            >
                <svg class="h-6 w-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </x-filament::avatar.user>

            <div class="flex-1">
                <h2 class="text-sm font-medium text-gray-950 dark:text-white">
                    Terminal TPV
                </h2>
            </div>

            <x-filament::button
                tag="a"
                :href="route('pos.terminal')"
                color="gray"
                outlined
                size="sm"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </x-slot>
                Abrir
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
