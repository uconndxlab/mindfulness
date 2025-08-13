<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DayResource;
use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
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
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order') // drag and drop
            ->filters([
                SelectFilter::make('id')
                    ->label('Module')
                    ->options(fn () => Module::pluck('name', 'id'))
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_days')
                    ->label('View Days')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (Module $record): string => DayResource::getUrl('index', ['tableFilters[module][value]' => $record->id]))
                    ->color('info')
                    ->visible(false), // disable for now
                Tables\Actions\EditAction::make()->slideOver(),
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
