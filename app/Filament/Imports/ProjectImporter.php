<?php

namespace App\Filament\Imports;

use App\Models\Project;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProjectImporter extends Importer
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:128']),
            ImportColumn::make('intro')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('content'),
            ImportColumn::make('website_title'),
            ImportColumn::make('website_url')
                ->rules(['nullable', 'url']),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): Project
    {
        $keyName = app(static::getModel())->getKeyName();
        $keyColumnName = $this->columnMap[$keyName] ?? $keyName;

        $record = isset($this->data[$keyColumnName])
            ? static::getModel()::find($this->data[$keyColumnName])
            : null;

        $project = $record ?? new Project;

        $userId = $this->import?->user_id ?? Auth::id();

        if ($userId) {
            if (! $project->exists) {
                $project->created_by_user_id = $userId;
            }
            $project->updated_by_user_id = $userId;
        }

        return $project;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
