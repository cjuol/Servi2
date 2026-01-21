<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament::avatar
            :src="asset('images/pos-tablet.png')"
            alt="Terminal TPV"
            size="lg"
        />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                Terminal TPV
            </h2>

            <p class="fi-account-widget-user-name">
                Gestión de sala
            </p>
        </div>

        <x-filament::button
            :href="route('pos.terminal')"
            color="gray"
            icon="heroicon-m-arrow-top-right-on-square"
            outlined
            tag="a">
            Abrir
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>
