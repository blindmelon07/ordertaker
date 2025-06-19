<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
             Group::make()->schema([
                Section::make('Product Information')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                 ->afterStateUpdated(fn (string $operation, $state, Set $set)=> $operation
                      === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated()
                    ->unique(Product::class, ignoreRecord: true, column: 'slug'),
                MarkdownEditor::make('description')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->fileAttachmentsDirectory('products'),
                ])->columns(2),
                Section::make('Images')->schema([
                    Forms\Components\FileUpload::make('image') 
                         ->multiple()
                        ->directory('products')
                        ->maxfiles(5)
                        ->reorderable(),
                ]),
             ])->columnSpan(2),
             Group::make()->schema([
                Section::make('Price')->schema([
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1000000)
                        ->default(0)
                        ->prefix('₱'),
                ]),
                Section::make('Associations')->schema([
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                        Select::make('brand_id')
                        ->label('Brand')
                        ->relationship('brand', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                ]),
                Section::make('status')->schema([
                     Toggle::make('in_stock')
                        ->label('In Stock')
                        ->default(true),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                    Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),
                   
                    Toggle::make('on_sale')
                        ->label('On Sale')
                        ->default(false),
                ]),
             ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                     Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                    Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
                    Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
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
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                      SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
