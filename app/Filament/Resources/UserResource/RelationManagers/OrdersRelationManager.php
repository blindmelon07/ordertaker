<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('Order ID')
                ->searchable()
                ,
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
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
               Tables\Actions\Action::make('View Order')
               ->url(fn(Order $record):string => OrderResource::getUrl('view', ['record' => $record]))
               ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
