<?php

return [
    'pattern' => 'records/example/example/(:any)',
    'action' => function ($id) {
        $language = get('language', 'nl');
        
        $item = [];
        $baseUrl = option('libis.solis-records.solis-baseUrl');
        $request = Remote::get($baseUrl . '/example/' . $id .'?depth=5&from_cache=0&language=' . $language);
        if ($request->code() === 200) {
            $item = $request->json(false);
        }

        $structure = include dirname(__DIR__, 2) . '/pageStructures/exampleStructure.php';
        $main = $structure['main'];
        $titleSelector = $structure['edit']['titleSelector'];
        $tabs = $structure['edit']['tabs'];
        $amountOfColumns = $structure['edit']['amountOfColumns'];
        $fields = $structure['edit']['fields'];

        $user = kirby()->user();
        $role = $user?->role();
        $roleId = $role?->id();

        return [
            'component' => 'k-detail-of-one-record-view',
            'breadcrumb' => [
                [
                    'label' => 'Example: ' . (isset($item[0]->tag->tag_name) ? $item[0]->tag->tag_name : ''),
                    'link'  => 'records/example/example/' . $id
                ]
            ],
            'props' => [
                'recordData' => $item,
                'mainInfo' => $main,
                'titleSelector' => $titleSelector,
                'tabs' => $tabs,
                'amountOfColumns' => $amountOfColumns,
                'fields' => $fields,
                'role' => $roleId
            ]
        ];
    }
];