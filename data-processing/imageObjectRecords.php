<?php

function postImageObject($data) {
    if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'editor' || kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {

        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><strong><em><ul><ol><li><br><sup><a><img><blockquote><dd><dt><dl><hr><pre><b><cite><i><span><sub><th><tr><wbr>';

        try {
            $data = sanitizeData($data, $allowedTags);
            $jsonData = json_encode($data);

            $baseUrl = option('libis.solis-records.solis-baseUrl');
            $url = $baseUrl . '/image_objects';

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
            if($result['data'][0]['id'] === null) {
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
function putImageObject($data, $id) {
    if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'editor' || kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {

        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><strong><em><ul><ol><li><br><sup><a><img><blockquote><dd><dt><dl><hr><pre><b><cite><i><span><sub><th><tr><wbr>';
        
        try {
            $data = sanitizeData($data, $allowedTags);
            $jsonData = json_encode($data);

            $baseUrl = option('libis.solis-records.solis-baseUrl');
            $url = $baseUrl . '/image_objects/' . $id;
            
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
function deleteImageObject($id) {
    if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {
        try {
            $baseUrl = option('libis.solis-records.solis-baseUrl');
            $url = $baseUrl . '/image_objects';
            
            try {
                $response = Remote::request($url . '/' . $id, [
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