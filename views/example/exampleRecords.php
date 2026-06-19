<?php

return [
    'pattern' => 'records/example',
    'action' => function () {
        $structure = include dirname(__DIR__, 2) . '/pageStructures/exampleStructure.php';
        $main = $structure['main'];
        $tabs = $structure['list']['tabs'];
        $titleSelector = $structure['list']['titleSelector'];
        $infoSelector = $structure['list']['infoSelector'];

        $user = kirby()->user();
        $role = $user?->role();
        $roleId = $role?->id();

        return [
            'component' => 'k-list-of-records-view',
            'breadcrumb' => [
                [
                    'label' => "Example " . t("libis.solis.records"),
                    'link'  => 'records/example'
                ]
            ],
            'props' => [
                'mainInfo' => $main,
                'tabs' => $tabs,
                'titleSelector' => $titleSelector,
                'infoSelector' => $infoSelector,
                'role' => $roleId
            ]
        ];
    }
];