<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
protected static ?int $navigationSort = 5;
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
                            ->preload()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount',Product::find($state)?->price ?? 0))
                            ->afterStateUpdated(fn($state, Set $set) => $set('total_amount',Product::find($state)?->price ?? 0))
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->afterStateUpdated(fn($state, Set $set, Get $get)=>$set('total_amount', $state * $get('unit_amount')))
                             ->columnSpan(2)
                             ->reactive(),
                        Forms\Components\TextInput::make('unit_amount')
                            ->required()
                            ->numeric()
                          ->disabled()
                          ->dehydrated()
                           ->columnSpan(3),
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(3),
                    ])->columns(12),
                  Placeholder::make('grand_total_placeholder')
                         ->label('Grand Total')
                         ->content(function (Get $get, Set $set) {
                              $total = 0;
                             if (!$repeaters = $get('items')) {
                               return $total;
                         }
                              foreach ($repeaters as $key => $repeater) {
                             $total += $get("items.{$key}.total_amount") ?? 0;
                          }
                          $set('grand_total', $total);
                          return Number::currency($total, 'PHP');
                          }),
                          Hidden::make('grand_total')
                          ->default(0)

                     
                         ])
                         ])->columnSpanFull()
                         ]);
                        }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                   ->searchable()
                    ->label('Customer')
                    ->sortable(),
                 Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->money('PHP')
                    ->label('Grand Total')
                   ->searchable(),
                   
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                   ->sortable() ->searchable(),
                    Tables\Columns\TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('shipping_method')
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                 
                    ->sortable()
                    ->searchable(),
               
                // Tables\Columns\TextColumn::make('shipping_amount')
                //     ->numeric()
                //     ->sortable(),
                
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
                  Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            RelationManagers\AddressRelationManager::class,
            
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return Order::count() > 0 ? (string) Order::count() : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count()>10 ? 'danger' : 'success';
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
