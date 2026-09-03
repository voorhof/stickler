<?php

return [

    'single' => [

        'label' => 'Permanent verwijderen',

        'modal' => [

            'heading' => ':Label permanent verwijderen',

            'actions' => [

                'delete' => [
                    'label' => 'Verwijderen',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Verwijderd',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Permanent verwijderen geselecteerd',

        'modal' => [

            'heading' => 'Geselecteerde :label permanent verwijderen',

            'actions' => [

                'delete' => [
                    'label' => 'Verwijderen',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Verwijderd',
            ],

            'deleted_partial' => [
                'title' => ':count van :total verwijderd',
                'missing_authorization_failure_message' => 'Je hebt geen toestemming om :count te verwijderen.',
                'missing_processing_failure_message' => ':count kon niet worden verwijderd.',
            ],

            'deleted_none' => [
                'title' => 'Verwijderen mislukt',
                'missing_authorization_failure_message' => 'Je hebt geen toestemming om :count te verwijderen.',
                'missing_processing_failure_message' => ':count kon niet worden verwijderd.',
            ],

        ],

    ],

];
