<?php
return [
    'main' => [
        'requestsInKirbyLink' => '/records/example/example', // used to link back to when for example the record is created
        'type' => 'example', // name of record in solis
        'singularType' => 'example', // singular type of the record in solis
        'searchType' => 'example', // value that needed to be filled in when you search on this record type in solis
    ],
    // data when the screen is for adding a record of this type
    'add' => [
        // the tabs on top of the screen of this page
        'tabs' => [
            [ 'name' => 'list', 'label' => str_replace("{type}", "example", t("libis.solis.nav.all.records")), 'link' => '/records/example' ],
            [ 'name' => 'add', 'label' => str_replace("{type}", "example", t("libis.solis.nav.add.record")), 'link' => '/records/example/toevoegen' ],
        ],
        'amountOfColumns' => '2', // 1 or 2 do you all the values under eachother  or in 2 columns (1/2)
        'fields' => [
            // more explenation on github on how to configure this
            [
                'name' => 'tag',    // name of field in SOLIS to write it to solis
                'align' => 'left',  // if in 2 columns on wich side does it come right or left
                'type' => 'entity', // the type of field you want to use
                'subType' => 'relation-field', // in some field types you have a sub type
                'label' => 'Tag', // the label that needs to been shown on top of the field
                // for the search
                'componentsOptions' => [
                    'textValue' => 'tag_name', // the text value we need to show of this sub entity (only with complex fields)
                    'infoValue' => 'alternate_name', // the info value we need to show of this sub entity (only with complex fields)
                    'recordType' => 'tag', // the value type
                    'min' => 1, // is there a max and min
                    'max' => 1
                ],
                'solis-selector' => 'tag', // name of field in SOLIS to get the record in solis
                // for showing the data after loading the screen
                'dataFields' => [
                    'id' => 'id',
                    'text' => 'tag_name',
                    'info' => 'alternate_name',
                ],
                'style' => '--width: 1/1;', // inside the comn the field can also be smaller then the column
                'url' => '/panel/records/tags/tag/{id}' // if the record is clicked where can we find more info of the record
            ],
        ]
    ],
    // data when the screen is for editing a record of this type
    'edit' => [
        'titleSelector' => "tag.tag_name", // which field do you want to show on top of the screen
        // the tabs on top of the screen of this page
        'tabs' => [
            [ 'name' => 'list', 'label' => str_replace("{type}", "example", t("libis.solis.nav.all.records")), 'link' => '/records/example' ],
            [ 'name' => 'add', 'label' => str_replace("{type}", "Example", t("libis.solis.nav.add.record")), 'link' => '/records/example/toevoegen' ],
            [ 'name' => 'record', 'label' => str_replace("{type}", "Example", t("libis.solis.nav.record")), 'link' => '/records/example/example/{id}' ],
        ],
        'amountOfColumns' => '2',
        'fields' => [
            // same structure then with adding records more info on github on how to create it
            [
                'name' => 'tag',
                'align' => 'left',
                'type' => 'entity',
                'subType' => 'relation-field',
                'label' => 'Tag',
                'componentsOptions' => [
                    'textValue' => 'tag_name',
                    'infoValue' => 'alternate_name',
                    'recordType' => 'tag',
                    'min' => 1,
                    'max' => 1
                ],
                'solis-selector' => 'tag',
                'dataFields' => [
                    'id' => 'id',
                    'text' => 'tag_name',
                    'info' => 'alternate_name',
                ],
                'style' => '--width: 1/1;',
                'url' => '/panel/records/tags/tag/{id}'
            ]
        ]
    ],
    // the page where there is an overview of all the records of that type
    'list' => [
        'titleSelector' => "tag.tag_name", // in the list you can show a title of the record, how to get this (the data comes from the search)
        'infoSelector' => "role.value", // same as with title but a value to give the user a little bit more context
        // the tabs on top of the screen of this page
        'tabs' => [
            [ 'name' => 'list', 'label' => str_replace("{type}", "example", t("libis.solis.nav.all.records")), 'link' => '/records/example' ],
            [ 'name' => 'add', 'label' => str_replace("{type}", "example", t("libis.solis.nav.add.record")), 'link' => '/records/example/toevoegen' ],
        ],
    ]
];