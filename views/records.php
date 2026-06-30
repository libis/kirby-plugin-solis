<?php

return [
    'pattern' => 'records',
    'action' => function () {
        return [
            'component' => 'k-records-view',
            'props' => [
                'recordsTypes' => [
                    [
                        'text' => 'Example',
                        'link' => 'records/example',
                        'image' => [
                            'ratio' => '21/9',
                            'back' => 'black',
                            'icon' => 'tag'
                        ],
                    ],
                    [
                        'text' => 'CodeTables',
                        'link' => 'records/codetables',
                        'image' => [
                            'ratio' => '21/9',
                            'back' => 'black',
                            'icon' => 'code'
                        ]
                    ],
                ]
            ]
        ];
    }
];