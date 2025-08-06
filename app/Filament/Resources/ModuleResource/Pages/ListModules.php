<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Models\Module;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // buttons that go above the table
        ];
    }

    public function reorderTable(array $order): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        
        Module::setNewOrder($order);

        $this->dispatch('reordered');
    }
}
