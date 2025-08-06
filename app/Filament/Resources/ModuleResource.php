<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DayResource;
use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
use App\Services\ModuleOrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('workbook_path')
                    ->label('Workbook')
                    ->disk('public')
                    ->directory('workbooks')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable(),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(fn () => ModuleOrderService::getNextOrder())
                    ->rules(['required', 'numeric', 'min:1'])
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if (!$state) {
                            return $set('order_confirmation', false);
                        }
                        // check if order already exists
                        $recordId = $record ? $record->id : null;
                        if (ModuleOrderService::orderExists($state, $recordId)) {
                            $set('order_confirmation', true);
                        } else {
                            $set('order_confirmation', false);
                        }
                    }),
                Forms\Components\Toggle::make('order_confirmation')
                    ->live()
                    ->label('I understand this will reorder existing modules')
                    ->visible(fn ($get) => $get('order_confirmation') === true)
                    ->required(fn ($get) => $get('order_confirmation') === true)
                    ->helperText('This order already exists. Confirming will insert this module, and shift all subsequent modules.')
                    ->default(false)
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Module $record): string => $record->description)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->hidden(),
                Tables\Columns\TextColumn::make('workbook_path')
                    ->label('Workbook')
                    ->default('None')
                    ->searchable(),
            ])->defaultSort('order', 'asc')
            ->filters([
                SelectFilter::make('id')
                    ->label('Module')
                    ->options(fn () => Module::pluck('name', 'id'))
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        // need to insert if order already exists
                        if (isset($data['order']) && ModuleOrderService::orderExists($data['order'])) {
                            ModuleOrderService::insertAtOrder($data['order']);
                        }
                        unset($data['order_confirmation']);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_days')
                    ->label('View Days')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (Module $record): string => DayResource::getUrl('index', ['tableFilters[module][value]' => $record->id]))
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, Module $record): array {
                        $originalOrder = $record->order;
                        $newOrder = $data['order'];

                        // need to insert if order has changed and new order already exists
                        if ($originalOrder !== $newOrder && ModuleOrderService::orderExists($newOrder, $record->id)) {
                            ModuleOrderService::insertAtOrder($newOrder, $record->id);
                        }
                        unset($data['order_confirmation']);
                        return $data;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
        ];
    }
}
