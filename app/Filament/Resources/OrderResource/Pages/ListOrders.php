<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
    


    public function getTabs(): array
    {
        return [
            null => Tab::make('All Orders'),
            'new' => Tab::make('New Orders')
                ->query(fn (Builder $query) => $query->where('status', 'new')),
            'processing' => Tab::make('Processing')
                ->query(fn (Builder $query) => $query->where('status', 'processing')),
            'shipped' => Tab::make('Shipped')
                ->query(fn (Builder $query) => $query->where('status', 'shipped')),
            'cancelled' => Tab::make('Cancelled')
                ->query(fn (Builder $query) => $query->where('status', 'cancelled')),
            'completed' => Tab::make('Completed')
                ->query(fn (Builder $query) => $query->where('status', 'completed')),

              
        ];
    }
}
