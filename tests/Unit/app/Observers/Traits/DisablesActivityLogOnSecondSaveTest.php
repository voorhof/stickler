<?php

/** @noinspection PhpMissingFieldTypeInspection */

use App\Observers\Traits\DisablesActivityLogOnSecondSave;

uses()->group('observers');

it('disables activity logging when model was recently created', function () {
    $observer = new class
    {
        use DisablesActivityLogOnSecondSave;
    };

    $model = new class extends Illuminate\Database\Eloquent\Model
    {
        use DisablesActivityLogOnSecondSave;

        public bool $enableLoggingModelsEvents = true;

        protected $table = 'posts';

        public function disableLogging(): self
        {
            $this->enableLoggingModelsEvents = false;

            return $this;
        }
    };

    $model->exists = true;
    $model->wasRecentlyCreated = true;

    expect($model->enableLoggingModelsEvents)->toBeTrue();

    $observer->disableActivityLogIfRecentlyCreated($model);

    expect($model->enableLoggingModelsEvents)->toBeFalse();
});

it('does not disable activity logging when model was not recently created', function () {
    $observer = new class
    {
        use DisablesActivityLogOnSecondSave;
    };

    $model = new class extends Illuminate\Database\Eloquent\Model
    {
        use DisablesActivityLogOnSecondSave;

        public bool $enableLoggingModelsEvents = true;

        protected $table = 'posts';

        public function disableLogging(): self
        {
            $this->enableLoggingModelsEvents = false;

            return $this;
        }
    };

    $model->exists = true;
    $model->wasRecentlyCreated = false;

    expect($model->enableLoggingModelsEvents)->toBeTrue();

    $observer->disableActivityLogIfRecentlyCreated($model);

    expect($model->enableLoggingModelsEvents)->toBeTrue();
});

it('does nothing when model does not have disableLogging method', function () {
    $observer = new class
    {
        use DisablesActivityLogOnSecondSave;
    };

    $model = new class extends Illuminate\Database\Eloquent\Model
    {
        protected $table = 'posts';
    };
    $model->exists = true;
    $model->wasRecentlyCreated = true;

    $observer->disableActivityLogIfRecentlyCreated($model);

    expect(true)->toBeTrue();
});

it('updates the created activity attributes when model was recently created', function () {
    $observer = new class
    {
        use DisablesActivityLogOnSecondSave;
    };

    $model = new class extends Illuminate\Database\Eloquent\Model
    {
        use DisablesActivityLogOnSecondSave;

        public bool $enableLoggingModelsEvents = true;

        protected $table = 'posts';

        public function disableLogging(): self
        {
            $this->enableLoggingModelsEvents = false;

            return $this;
        }

        public function activitiesAsSubject(): self
        {
            return $this;
        }

        public function where(): self
        {
            return $this;
        }

        public function latest(): self
        {
            return $this;
        }

        public function first(): ?object
        {
            static $called = false;
            if ($called) {
                return null;
            }
            $called = true;

            $activity = new class
            {
                public $properties;

                public function save(): void {}
            };
            $activity->properties = new Illuminate\Support\Collection(['event' => 'created']);

            return $activity;
        }
    };

    $model->exists = true;
    $model->wasRecentlyCreated = true;
    $model->setRawAttributes(['content' => '<p>Rich editor content</p>']);

    $observer->disableActivityLogIfRecentlyCreated($model);

    expect($model->enableLoggingModelsEvents)->toBeFalse();
});

it('does not update activity attributes when model does not have activitiesAsSubject method', function () {
    $observer = new class
    {
        use DisablesActivityLogOnSecondSave;
    };

    $model = new class extends Illuminate\Database\Eloquent\Model
    {
        use DisablesActivityLogOnSecondSave;

        public bool $enableLoggingModelsEvents = true;

        protected $table = 'posts';

        public function disableLogging(): self
        {
            $this->enableLoggingModelsEvents = false;

            return $this;
        }
    };

    $model->exists = true;
    $model->wasRecentlyCreated = true;

    $observer->disableActivityLogIfRecentlyCreated($model);

    expect($model->enableLoggingModelsEvents)->toBeFalse();
});
