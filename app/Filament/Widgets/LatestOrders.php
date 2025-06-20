<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                  Tables\Columns\TextColumn::make('id')
                ->label('Order ID')
                ->searchable()
                ,
                TextColumn::make('user.name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->badge()
                    ->money('PHP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn(string $state):string => match ($state){
                        'new' => 'primary',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'success',
                        
                    })
                    ->icon(fn(string $state):string => match ($state){
                        'new' => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-cog',
                        'shipped' => 'heroicon-o-truck',
                        'cancelled' => 'heroicon-o-x-circle',
                        'completed' => 'heroicon-o-check-circle',
                    })
                    ->sortable(),
                    TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Order Date'),
            ])
            ->actions([
                Tables\Actions\Action::make('View Order')
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
