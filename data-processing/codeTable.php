<?php

return [
    [
        'pattern' => '/records/codetables/(:any)/(:any)/(:any)',
        'method' => 'PUT', 
        'action' => function ($type, $id, $language) {
            if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'editor' || kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {
                header('Content-Type: application/json');
                
                $decodedData = json_decode(file_get_contents("php://input"), true);
                if (!is_array($decodedData)) {
                    echo json_encode(['status' => 'error', 'message' => t('libis.solis.error.invalid.JSON')]);
                    return;
                }

                $allowedTags = '<h1><h2><h3><h4><h5><h6><p><strong><em><ul><ol><li><br><sup><a><img><blockquote><dd><dt><dl><hr><pre><b><cite><i><span><sub><th><tr><wbr>';
                
                try {
                    $data = sanitizeData($decodedData, $allowedTags);
                    $jsonData = json_encode($data);

                    $baseUrl = option('libis.solis-records.solis-baseUrl');
                    $url = $baseUrl . '/' . $type . '/' . $id . '?language=' . $language;
                    
                    try {
                        $response = Remote::request($url, [
                            'method'  => 'PUT',
                            'data' => $jsonData,
                            'timeout' => 0,
                            'headers' => [
                                'content-type' => 'application/json',
                                'X-API-KEY: ' . option('libis.solis-records.solis-apiKey'),
                            ]
                        ]);
                    } 
                    catch (Exception $e) {
                        http_response_code(502);
                        return [
                            'status' => 'error',
                            'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                        ];
                    }
                    
                    $result = json_decode($response->content(), true);
                    if($result['data']['id'] === null) {
                    return[
                            'status' => 'error',
                            'message' => t("libis.solis.error.solis.request.failed.wh.code"),
                        ]; 
                    }

                    return[
                        'status' => 'success',
                        'result' => $result,
                        'message' => 'succes',
                    ];
                }
                catch (Exception $e) {
                    http_response_code(400);
                    return [
                        'status' => 'error',
                        'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                    ];
                }
            }
        }
    ],
    [
        'pattern' => '/records/codetables/(:any)/(:any)',
        'method' => 'POST', 
        'action' => function ($type, $language) {
            if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'editor' || kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {
                header('Content-Type: application/json');
                
                $decodedData = json_decode(file_get_contents("php://input"), true);
                if (!is_array($decodedData)) {
                    echo json_encode(['status' => 'error', 'message' => t('libis.solis.error.invalid.JSON')]);
                    return;
                }

                $allowedTags = '<h1><h2><h3><h4><h5><h6><p><strong><em><ul><ol><li><br><sup><a><img><blockquote><dd><dt><dl><hr><pre><b><cite><i><span><sub><th><tr><wbr>';
                
                try {
                    $data = sanitizeData($decodedData, $allowedTags);
                    $jsonData = json_encode($data);

                    $baseUrl = option('libis.solis-records.solis-baseUrl');
                    $url = $baseUrl . '/' . $type . '?language=' . $language;
                    
                    try {
                        $response = Remote::request($url, [
                            'method'  => 'POST',
                            'data' => $jsonData,
                            'timeout' => 0,
                            'headers' => [
                                'content-type' => 'application/json',
                                'X-API-KEY: ' . option('libis.solis-records.solis-apiKey'),
                            ]
                        ]);
                    } 
                    catch (Exception $e) {
                        http_response_code(502);
                        return [
                            'status' => 'error',
                            'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                        ];
                    }

                    $result = json_decode($response->content(), true);

                    if($result['data'][0]['id'] !== null) {
                        $id = $result['data'][0]['id'];
                        $data['id'] = $id;
                        $jsonData = json_encode($data);

                        foreach(kirby()->languages() as $kirbylanguage) {
                            $newResult = [];
                            if($kirbylanguage->code() != $language) {
                                $url = $baseUrl . '/' . $type . '?language=' . $kirbylanguage->code();
                                try {
                                    $response = Remote::request($url, [
                                        'method'  => 'POST',
                                        'data' => $jsonData,
                                        'timeout' => 0,
                                        'headers' => [
                                            'content-type' => 'application/json',
                                            'X-API-KEY: ' . option('libis.solis-records.solis-apiKey'),
                                        ]
                                    ]);
                                } 
                                catch (Exception $e) {
                                    http_response_code(502);
                                    return [
                                        'status' => 'error',
                                        'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                                    ];
                                }
                                $newResult = json_decode($response->content(), true);
                                if ($newResult['data'][0]['id'] == null) {
                                    return ['status' => 'error', 'message' => t("libis.solis.error.solis.request.failed.translation")];
                                }
                            }
                        }
                    }
                    else {
                    return[
                            'status' => 'error',
                            'message' => t("libis.solis.error.solis.request.failed.wh.code"),
                        ]; 
                    }

                    return[
                        'status' => 'success',
                        'result' => $result,
                    ];
                } 
                catch (Exception $e) {
                    http_response_code(400);
                    return [
                        'status' => 'error',
                        'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                    ];
                }
            }
        }
    ],
    [
        'pattern' => '/records/codetables/(:any)/(:any)',
        'method' => 'DELETE', 
        'action' => function ($type, $id) {
            if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {
                try {
                    $baseUrl = option('libis.solis-records.solis-baseUrl');
                    $url = $baseUrl . '/' . $type . '/' . $id;
                    
                    try {
                        $response = Remote::request($url, [
                            'method'  => 'DELETE',
                            'timeout' => 0,
                            'headers' => [
                                'content-type' => 'application/json',
                                'X-API-KEY: ' . option('libis.solis-records.solis-apiKey'),
                            ]
                        ]);
                    } 
                    catch (Exception $e) {
                        http_response_code(502);
                        return [
                            'status' => 'error',
                            'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                        ];
                    }

                    return[
                        'status' => 'success',
                        'result' => json_decode($response->content(), true),
                    ];
                } 
                catch (Exception $e) {
                    http_response_code(400);
                    return [
                        'status' => 'error',
                        'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
                    ];
                }
            }
        }
    ]
];