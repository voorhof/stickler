<?php

namespace App\Filament\Traits;

use Asmit\ResizedColumn\Models\TableSetting;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

trait CanResetTable
{
    public function resetColumnWidths(): void
    {
        $resourceModelPath = static::getResource()::getModel();

        // Reset the table settings (column width, filters, sorting)
        TableSetting::query()
            ->where('user_id', auth()->id())
            ->where('resource', $resourceModelPath)
            ->delete();

        $modelName = Str::snake(class_basename($resourceModelPath));
        session()->forget("tables.{$modelName}_columns_style");

        // Reset the table column manager
        if (method_exists($this, 'resetTableColumnManager')) {
            $this->resetTableColumnManager();
            $this->dispatch('reset-table-column-manager');
        }

        Notification::make()
            ->title(__('Table has been reset.'))
            ->success()
            ->send();

        $this->redirect($this->getUrl());
    }
}
