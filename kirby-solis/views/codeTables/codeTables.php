<?php

return [
    'pattern' => 'records/codetables',
    'action' => function () {
        $language = get('language', 'nl');

        $structure = include dirname(__DIR__, 2) . '/pageStructures/codeTableStructure.php';
        $main = $structure['main'];
        $codeTables = $structure['codeTables'];
        foreach($codeTables as &$codeTable) {
            $solisSelector = $codeTable['solisSearchRecord'];
            $values = [];
            $baseUrl = option('libis.solis-records.solis-baseUrl');
            $request = Remote::get($baseUrl . '/_logic/codetable?name=' . $solisSelector .'&language=' . $language . '&from_cache=0');
            if ($request->code() === 200) {
                $values = $request->json(false);
            }
            $values = array_map(function($value) {
                $text = isset($value->value) ? $value->value : '';
                $value = isset($value->id) ? $value->id : '';
                return ['text' => $text, 'value' => $value];
            }, $values) ;      
            usort($values, function ($a, $b) {
                return strcmp($a['text'], $b['text']);
            });

            $codeTable['values'] = $values;
        }

        $user = kirby()->user();
        $role = $user?->role();
        $roleId = $role?->id();

        return [
            'component' => 'k-codetables-view',
            'breadcrumb' => [
                [
                    'label' => t("libis.solis.codeTables"),
                    'link'  => 'records/codetables'
                ]
            ],
            'props' => [
                'codeTables' => $codeTables,
                'mainInfo' => $main,
                'role' => $roleId
            ]
        ];
    }
];