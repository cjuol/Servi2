<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class PosAccessWidget extends Widget
{
    protected static ?int $sort = -1;

    protected int | string | array $columnSpan = 1;

    public function render(): View
    {
        return view('filament.widgets.pos-access-widget');
    }
}
