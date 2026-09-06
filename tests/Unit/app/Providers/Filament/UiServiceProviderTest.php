<?php

use App\Providers\Filament\UiServiceProvider;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;

test('it extends ServiceProvider', function () {
    $provider = new UiServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(UiServiceProvider::class);
});

test('it executes register method without errors', function () {
    $provider = new UiServiceProvider(app());

    $provider->register();

    expect(true)->toBeTrue();
});

test('it executes boot method without errors', function () {
    $provider = new UiServiceProvider(app());

    $provider->boot();

    expect(true)->toBeTrue();
});

test('it registers toolbar column manager trigger after render hook', function () {
    expect(FilamentView::hasRenderHook(
        TablesRenderHook::TOOLBAR_COLUMN_MANAGER_TRIGGER_AFTER,
    ))->toBeTrue();
});

test('it configures base columns to be toggleable by default', function () {
    $column = Column::make('test_column');

    expect($column->isToggleable())->toBeTrue();
});

test('it configures text columns to be searchable and sortable by default', function () {
    $column = TextColumn::make('test_text_column');

    expect($column->isSearchable())->toBeTrue()
        ->and($column->isSortable())->toBeTrue()
        ->and($column->isToggleable())->toBeTrue();
});

test('it configures icon columns to be sortable, boolean, and center aligned by default', function () {
    $column = IconColumn::make('test_icon_column');

    expect($column->isSortable())->toBeTrue()
        ->and($column->isBoolean())->toBeTrue()
        ->and($column->getAlignment())->toBe(Alignment::Center);
});

test('it configures notifications with a 10000ms duration', function () {
    $notification = Notification::make();

    expect($notification->getDuration())->toBe(10000);
});

test('it configures date pickers with default min and max dates', function () {
    $picker = DatePicker::make('test_date');

    expect($picker->getMinDate())->toEqual(Carbon::createFromDate(1900, 1, 1))
        ->and($picker->getMaxDate())->not->toBeNull();
});

test('it configures datetime pickers without seconds by default', function () {
    $picker = DateTimePicker::make('test_datetime');

    expect($picker->hasSeconds())->toBeFalse();
});

test('it configures rich editors with attachments settings and toolbar buttons', function () {
    $schema = Schema::make(Mockery::mock(Component::class, HasSchemas::class));
    $editor = RichEditor::make('test_content')->container($schema);

    expect($editor->getFileAttachmentsDiskName())->toBe('public')
        ->and($editor->getFileAttachmentsDirectory())->toBe('attachments')
        ->and($editor->getFileAttachmentsVisibility())->toBe('public')
        ->and($editor->getFileAttachmentsAcceptedFileTypes())->toBe(['image/png', 'image/jpeg'])
        ->and($editor->getFileAttachmentsMaxSize())->toBe(5120)
        ->and($editor->getColumnSpan('default'))->toBe('full')
        ->and($editor->hasToolbarButton('attachFiles'))->toBeTrue()
        ->and($editor->hasToolbarButton('bold'))->toBeTrue()
        ->and($editor->hasToolbarButton('italic'))->toBeTrue()
        ->and($editor->hasToolbarButton('link'))->toBeTrue()
        ->and($editor->hasToolbarButton('blockquote'))->toBeTrue()
        ->and($editor->hasToolbarButton('strike'))->toBeTrue()
        ->and($editor->hasToolbarButton('undo'))->toBeTrue()
        ->and($editor->hasToolbarButton('redo'))->toBeTrue();
});

test('it configures actions to not close modal on click away and to slide over', function () {
    $action = Action::make('test_action');

    expect($action->isModalClosedByClickingAway())->toBeFalse()
        ->and($action->isModalSlideOver())->toBeTrue();
});

test('it configures schemas with default date and time display formats', function () {
    $schema = Schema::make(Mockery::mock(Component::class, HasSchemas::class));

    expect($schema->getDefaultDateDisplayFormat())->toBe('Y-m-d')
        ->and($schema->getDefaultDateTimeDisplayFormat())->toBe('h:i A')
        ->and($schema->getDefaultTimeDisplayFormat())->toBe('Y-m-d h:i A');
});

test('it configures tables with default options', function () {
    $table = Table::make(Mockery::mock(Component::class, HasSchemas::class, HasTable::class));

    expect($table->getPaginationPageOptions())->toBe([10, 25, 50, 100])
        ->and($table->getDefaultDateDisplayFormat())->toBe('d/m/Y')
        ->and($table->getDefaultDateTimeDisplayFormat())->toBe('d/m/Y H:i')
        ->and($table->hasDeferredFilters())->toBeFalse()
        ->and($table->getFiltersFormWidth())->toBe(Width::Small)
        ->and($table->hasReorderableColumns())->toBeTrue();
});
