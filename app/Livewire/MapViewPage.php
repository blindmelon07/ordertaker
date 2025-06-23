<?php

namespace App\Livewire;

use Livewire\Component;

class MapViewPage extends Component
{
    public $lat = 13.41;
    public $lng = 122.55;

    public function render()
    {
        return view('livewire.map-view-page');
    }
}
