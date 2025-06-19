<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
             Group::make()->schema([
                Section::make('Order Information')->schema([
                  Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                Select::make('payment_method')
                        ->options([
                            'e-wallet' => 'E-Wallet',
                            'bank_transfer' => 'Bank Transfer',
                            'cash_on_delivery' => 'Cash on Delivery',
                        ])
                        ->required()
                        ->searchable(),
                Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                        ])
                        ->required()
                        ->searchable()
                        ->default('pending'),
                ToggleButtons::make('status')
                ->inline()
                        ->options([
                            'new' => 'New',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->colors([
                            'new' => 'info',
                            'processing' => 'warning',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'cancelled' => 'danger',

                        ])
                        ->icons([
                            'new' => 'heroicon-o-newspaper',
                            'processing' => 'heroicon-o-cog',
                            'shipped' => 'heroicon-o-truck',
                            'delivered' => 'heroicon-o-check-circle',
                            'cancelled' => 'heroicon-o-x-circle',
                        ])
                        ->required()
                        ->default('new'),
                        Select::make('currency')
                        ->options([
                            'PHP' => 'Philippine Peso (₱)',
                            'USD' => 'US Dollar ($)',
                            'EUR' => 'Euro (€)',
                        ])->default('PHP')
                        ->required(),
                        Select::make('shipping_method')
                        ->options([
                            'standard' => 'Standard Delivery',
                            'express' => 'Express Delivery',
                            'pickup' => 'Pickup',
                        ])
                        
                        ->default('standard'),
                        Textarea::make('notes')
                        ->columnSpanFull()
                ])->columns(2),
                Section::make('Order Items')->schema([
                    Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000000)
                            ->default(0)
                            ->prefix('₱'),
                    ])
                ])
             ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
