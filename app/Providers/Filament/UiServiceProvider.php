<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\Actions\AttachFilesAction;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    /**
     * Filament UI customization defaults
     * https://github.com/iAmKevinMcKee/roadmap/blob/lesson-4/app/Providers/FilamentUiServiceProvider.php
     */
    public function boot(): void
    {
        // Various table presets
        Table::configureUsing(function (Table $table) {
            return $table
                ->reorderableColumns()
                ->columnManagerColumns(1)
                ->columnManagerTriggerAction(fn (Action $action) => $action->button()->label(__('filament-tables::table.column_manager.heading')))
                ->filtersTriggerAction(fn (Action $action) => $action->button()->label(__('filament-tables::table.filters.heading'))->slideOver()->closeModalByClickingAway())
                ->filtersFormWidth(Width::Small)
                ->deferFilters(false)
                ->paginationPageOptions([10, 25, 50, 100])
                ->defaultDateDisplayFormat('d/m/Y')
                ->defaultDateTimeDisplayFormat('d/m/Y H:i');
        });

        // Register the resetColumnWidths button next to the column manager trigger
        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_COLUMN_MANAGER_TRIGGER_AFTER,
            fn () => view('filament.hooks.reset-column-widths-button'),
        );

        // Make all columns toggleable
        Column::configureUsing(function (Column $column) {
            return $column
                ->toggleable();
        });

        // Sort- and searchable all text columns
        TextColumn::configureUsing(function (TextColumn $textColumn) {
            return $textColumn
                ->searchable() // BE CAREFUL, you may end up with 500 errors
                ->sortable(); // BE CAREFUL, you may end up with 500 errors
        });

        IconColumn::configureUsing(function (IconColumn $iconColumn) {
            return $iconColumn
                ->sortable() // BE CAREFUL, you may end up with 500 errors
                ->boolean()
                ->alignCenter();
        });

        // Make notifications last 10 seconds by default
        Notification::configureUsing(function (Notification $notification) {
            return $notification->duration(10000);
        });

        // Use your preferred date displays
        Schema::configureUsing(function (Schema $schema) {
            return $schema
                ->defaultDateDisplayFormat('Y-m-d')
                ->defaultDateTimeDisplayFormat('h:i A')
                ->defaultTimeDisplayFormat('Y-m-d h:i A');
        });

        // Add sensible min and max dates so you don't end up with dates like 01/01/0000 or 01/01/3000
        DatePicker::configureUsing(function (DatePicker $datePicker) {
            return $datePicker
                ->minDate(Carbon::createFromDate(1900, 1, 1))
                ->maxDate(now()->addYears(50));
        });

        // remove seconds from the DateTimePicker
        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker) {
            return $dateTimePicker->seconds(false);
        });

        // Rich editor default settings
        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor
                ->toolbarButtons([
                    ['undo', 'redo'],
                    [ToolbarButtonGroup::make(__('Paragraph'), ['paragraph', 'h2', 'h3', 'h4'])],
                    [ToolbarButtonGroup::make(__('Alignment'), ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'])],
                    ['bold', 'italic', 'underline', 'strike', 'link', 'textColor', 'highlight'],
                    ['blockquote', 'codeBlock', ToolbarButtonGroup::make('Lists', ['bulletList', 'orderedList'])],
                    ['attachFiles'],
                    // The `customBlocks` and `mergeTags` tools are also added here if those features are needed in the future.
                ])
                ->floatingToolbars([
                    'paragraph' => [
                        'bold', 'italic', 'underline', 'strike', 'link', 'textColor',
                    ],
                    'heading' => [
                        'h2', 'h3', 'h4',
                    ],
                ])
                ->textColors([
                    '#000' => __('Black'),
                    '#fff' => __('White'),
                    ...TextColor::getDefaults(),
                ])
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('attachments')
                ->fileAttachmentsVisibility('public')
                ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg'])
                ->fileAttachmentsMaxSize(5120) // 5 MB
                ->resizableImages()
                ->registerActions([
                    // Make the text input field required
                    AttachFilesAction::make()
                        ->schema(fn (array $arguments, RichEditor $component): array => [
                            FileUpload::make('file')
                                ->label(filled($arguments['src'] ?? null)
                                    ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.existing')
                                    : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.new'))
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes($component->getFileAttachmentsAcceptedFileTypes())
                                ->maxSize($component->getFileAttachmentsMaxSize())
                                ->storeFiles(false)
                                ->required(blank($arguments['src'] ?? null))
                                ->hiddenLabel(blank($arguments['src'] ?? null)),
                            TextInput::make('alt')
                                ->label(filled($arguments['src'] ?? null)
                                    ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.existing')
                                    : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.new'))
                                ->maxLength(1000)
                                ->required(), // This is the only real change compared to the default, making the input field required
                        ]),
                ])
                ->tools([
                    // Update the icon to Heroicon::Photo
                    RichEditorTool::make('attachFiles')
                        ->label(__('filament-forms::components.rich_editor.tools.attach_files'))
                        ->action(arguments: '{ alt: $getEditor().getAttributes(\'image\')?.alt, id: $getEditor().getAttributes(\'image\')?.id, src: $getEditor().getAttributes(\'image\')?.src }')
                        ->activeKey('image')
                        ->icon(Heroicon::OutlinedPhoto) // Change to your preferred icon
                        ->iconAlias('forms:components.rich-editor.toolbar.attach-files'),
                ])
                ->columnSpanFull();
        });

        // If an action is a modal, do not close by clicking away and default to slide over
        Action::configureUsing(function (Action $action) {
            $action
                ->closeModalByClickingAway(false)
                ->slideOver();
        });
    }
}
