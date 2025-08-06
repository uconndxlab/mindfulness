<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleOrderService
{
    public static function insertAtOrder(int $order, ?int $excludeModuleId = null): void
    {
        DB::transaction(function () use ($order, $excludeModuleId) {
            $query = Module::where('order', '>=', $order);
            
            if ($excludeModuleId) {
                $query->where('id', '!=', $excludeModuleId);
            }
            
            $modulesToUpdate = $query->orderBy('order', 'asc')->get();
            
            $currentOrder = $order;
            foreach ($modulesToUpdate as $module) {
                if ($module->order > $currentOrder) {
                    break;
                }
                $module->increment('order');
                $currentOrder++;
            }
        });
    }
    
    public static function orderExists(int $order, ?int $excludeModuleId = null): bool
    {
        $query = Module::where('order', $order);
        
        if ($excludeModuleId) {
            $query->where('id', '!=', $excludeModuleId);
        }
        
        return $query->exists();
    }
    
    public static function getNextOrder(): int
    {
        return Module::max('order') + 1;
    }
} 