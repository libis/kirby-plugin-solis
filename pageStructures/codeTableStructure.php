<?php
return [
    'main' => [
        'requestsInKirbyLink' => '/records/codetables', // used to link back to when for example the record is created
        'name' => 'Codetables'
    ],
    'codeTables' => [
        [
            'name' => 'example', // what is the name of the record type in solis (used to write it to solis)
            'solisSearchRecord' => 'example', // value that needed to be filled in when you search on this record type in solis
            'label' => "Example", // label to show on top of that column
            'style' => '--width:1/3', // how much place can the column take in the view
        ]
    ]
]
?>