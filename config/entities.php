<?php
return [

    'clients' => [
        'model' => App\Models\Client::class,
        'title' => 'Client',
        'tabs' => [
            'Info',
            'Classifieds',
            'Contacts',
            'Contracts',
            'Files',
            'Notes',
            'Posters',
            'Pulse Picks',
            'Pulse Pick Drafts',
            'Paper Boy Contracts',
            'Paper Boy Contract Drafts',

        ],
        'label_field' => 'name',
    ],

    'users' => [
        'model' => App\Models\User::class,
        'title' => 'User',
        'tabs' => [
            'Info',
            'Timesheet'
        ],
        'label_field' => 'name',
    ],

    'vehicles' => [
        'model' => App\Models\Vehicle::class,
        'title' => 'Vehicle',
        'tabs' => [
            'Info',
           'Expenses',
        ],
        'label_field' => 'vin',
    ],

    'drivers' => [
        'model' => App\Models\User::class,
        'title' => 'Driver',
        'tabs' => [
            'Info',
            'Routes',
            'Timesheet'
        ],
        'label_field' => 'name',
    ],
    'locations' => [
        'model' => App\Models\Location::class,
        'title' => 'Location',
        'tabs' => [
            'Info',
        ],
        'label_field' => 'name',
    ],

];
?>