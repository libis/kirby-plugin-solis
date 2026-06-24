<?php

return [
    'pattern' => 'records/example/toevoegen',
    'action' => function () {
        $structure = include dirname(__DIR__, 2) . '/pageStructures/exampleStructure.php';
        $main = $structure['main'];
        $tabs = $structure['add']['tabs'];
        $amountOfColumns = $structure['add']['amountOfColumns'];
        $fields = $structure['add']['fields'];

        $user = kirby()->user();
        $role = $user?->role();
        $roleId = $role?->id();
        
        $allowed = ['editor', 'admin', 'supervisor'];
        $normalizedRoleId = is_string($roleId) ? strtolower(trim($roleId)) : $roleId;

        $hasPermission = in_array($normalizedRoleId, $allowed, true);

        if($hasPermission) {
            return [
                'component' => 'k-add-one-record-of-type',
                'breadcrumb' => [
                    [
                        'label' => str_replace("{type}", "About", t("libis.solis.nav.add.record")),
                        'link'  => 'records/example/toevoegen'
                    ]
                ],
                'props' => [
                    'mainInfo' => $main,
                    'tabs' => $tabs,
                    'amountOfColumns' => $amountOfColumns,
                    'fields' => $fields
                ]
            ];
        }
        else {
            return go('panel/records');
        }
    }
];