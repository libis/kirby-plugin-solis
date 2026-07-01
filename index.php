<?php

class HookBypass {
  public static bool $skipFileUpdateHook = false;
}


function sanitizeData($data, $allowedTags) {
  if (is_object($data)) {
      $objectVars = get_object_vars($data);
      foreach ($objectVars as $key => $value) {
        $data->$key = sanitizeData($value, $allowedTags);
      }
      return $data;
  }
  elseif (is_string($data)) {
    $data = trim($data, '"');

    if (!filter_var($data, FILTER_VALIDATE_URL)) {
      $data = stripslashes($data);
    }
    $clean = strip_tags($data, $allowedTags);

    if ($data !== $clean) {
      throw new Exception('Unsafe content detected');
    }
    return $clean;
  } 
  elseif (is_array($data)) {
      $result = [];
      foreach ($data as $key => $value) {
        $result[$key] = sanitizeData($value, $allowedTags);
      }
      return $result;
  } 
  else {
      return $data;
  }
}
require __DIR__ . '/data-processing/imageObjectRecords.php';
Kirby::plugin('libis/solis-records', [
  'fields' => [
    'add_multiple_values_field' => [],
    'multiple_records_of_type' => [],
    'relationDialog' => [
      'validations' => [
        'items-range' => function ($value) {
          if (is_string($value)) {
            $value = Yaml::decode($value) ?? [];
          }

          if (!is_array($value)) {
            $value = $value ? [$value] : [];
          }

          $count = count($value);
          $min = $this->min(); 
          $max = $this->max();

          if ($min !== null && $count < (int)$min) {
            throw new Exception(str_replace("{min}", $min, t("libis.solis.error.min.item")));
          }
          if ($max !== null && $count > (int)$max) {
            throw new Exception(str_replace("{max}", $min, t("libis.solis.error.max.item")));
          }
        },
      ]
    ],
    'imageUpload' => [
      'props' => [
        'pageId' => function () {
          return $this->model()->id();
        },
        'fileTemplate' => function ($template) {
          $blueprint = \Kirby\Cms\Blueprint::load("files/$template");
          return [
            'name'   => $template,
            'accept' => $blueprint['accept'] ?? null
          ];
        }
      ]
    ]
  ],
  'areas' => [
    'records' => [
      'label' => 'Records',
      'icon' => '',
      'menu' => true,
      'breadcrumbLabel' => 'Records',
      'views' => require __DIR__ . '/area-views/views.php',
    ]
  ],
  'hooks' => [
    'file.changeName:before' => function (Kirby\Cms\File $file, string $name) {
      throw new Exception(t("libis.solis.error.rename.not.allowed"));
    },
    'file.update:after' => function (Kirby\Cms\File $newFile, Kirby\Cms\File $oldFile) {
      if (HookBypass::$skipFileUpdateHook == true) {
        return; 
      }
      if($newFile->template() == 'solisImageTemplate') {
        // change this if needed (change if the values of an image are not the same)
        $solisId = $oldFile->solis_id();
        if($solisId != '') {
          $sendValues = [];
          if($newFile->title() != $oldFile->title()) {
            $sendValues['title'] = $newFile->title()->value();
          }
          if($newFile->alt_text() != $oldFile->alt_text()) {
            $sendValues['alt_text'] = $newFile->alt_text()->value();
          }
          if($newFile->image_object_name() != $oldFile->image_object_name()) {
            $sendValues['image_object_name'] = $newFile->image_object_name()->value();
          }

          if(!empty($sendValues)) {
            $response = putImageObject($sendValues, $solisId);

            if($response['status'] != "success") {
              HookBypass::$skipFileUpdateHook = true;
              $newFile->update([
                'title' => $oldFile->title(),
                'alt_text' => $oldFile->alt_text(),
                'image_object_name' => $oldFile->image_object_name(),
              ]);
              HookBypass::$skipFileUpdateHook = false;
              throw new Exception($response['message']);
            }
          }
        }
      }
    },
    'file.delete:before' => function (Kirby\Cms\File $file) {
      // change this if needed (change if the values of an image are not the same)
      if($file->template() == 'solisImageTemplate') {
        $solisId = $file->solis_id();
        if($solisId != '') {
          $response = deleteImageObject($solisId);
          if($response['status'] != "success") {
            throw new Exception($response['message']);
          }
        }
      }
    }
  ],
  'routes' => array_merge(
    require __DIR__ . '/data-processing/general.php',
    [
      [
        'pattern' => 'solis-records',
        'method' => 'GET',
        'action' => function () {
          $page = get('page', 1);
          $limit = get('limit', 20);
          $limit = (int)$limit;
          $from = $page != 1 ? (((int)$page - 1) * $limit) + 1 : 0;

          $query = get('q', '');
          $recordType = get('recordType', '');

          $recordType = $recordType == '' ? '' : 'type:' . $recordType . '';

          $query = $query == '' ? '' : 'any:*' . $query . '*';

          $baseUrl = get('baseUrl', '');
          $baseUrl = $baseUrl == '' ? option('libis.solis-records.solis-baseUrl') : $baseUrl;

          $searchQuery = "";
          if($query != '' && $recordType != '') {
            $searchQuery = $query . ' ' . $recordType;
          }
          elseif($query != '') {
            $searchQuery = $query;
          }
          else {
            $searchQuery = $recordType;
          }

          $items = [];
          $data = [];
          $apiUrl = $baseUrl . '/_search?' . http_build_query([
            'query' => $searchQuery,
            'from' => $from,
            'bulkSize' => $limit
          ]);
          if(kirby()->user() && kirby()->user()->isLoggedIn()) {
            $curl = curl_init($apiUrl);
            curl_setopt_array($curl, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . option('libis.solis-records.solis-apiKey'),
                'Accept: application/json',
              ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $data = json_decode($response, true);
            if($data) {
              $items = $data['docs'] ?? [];
            }
          }
          else {
            $request = Remote::get($apiUrl);
            if ($request->code() === 200) {
              $data = $request->json();
              $items = $data['docs'] ?? [];
            }
          }

          $info = $data['info'][0] ?? [];
          $total = $info['total'] ?? 0;
          $pages = ceil($total / $limit);
          $paginated = array_slice($items, ($page - 1) * $limit, $limit);

          return [
            'items' => $items,
            'pagination' => [
              'page' => (int)$page,
              'pages' => $pages,
              'limit' => $limit,
              'total' => $total
            ]
          ];
        }
      ],
      [
        'pattern' => 'all-solis-records',
        'method' => 'GET',
        'action' => function () {
          $api= get('api', '');
          if($api != '') {
            $baseUrl = option('libis.solis-records.solis-baseUrl');
            $request = Remote::get($baseUrl . $api);
            
            if ($request->code() === 200) {
              $data = $request->json();
              return $data;
            }
            return [];
          }
          else {
            return [];
          }
        }
      ],
      [
        'pattern' => 'solis-custom-image-upload',
        'method' => 'POST',
        'action' => function () {
          if(kirby()->user() && kirby()->user()->isLoggedIn() && kirby()->user()->isValid() && (kirby()->user()?->role()->id() == 'editor' || kirby()->user()?->role()->id() == 'supervisor' || kirby()->user()->isAdmin())) {
            try {
              $data = $_POST;
              $meta = json_decode(get('meta'), true);              
              $pageId = $data['pageId']   ?? null;
              $name = $data['name']     ?? null;
              $template = $data['template'] ?? null;

              $file = $_FILES['file'];

              $uploadPage = site()->find($data['pageId']);
              if($uploadPage) {
                $uploaded = $uploadPage->createFile([
                  'source'   => $file['tmp_name'],
                  'filename' => $data['name'] . '.' . $data['extension'],
                  'template' => $data['template']
                ]);

                if ($uploaded instanceof Kirby\Cms\File) {
                  HookBypass::$skipFileUpdateHook = true;
                  $uploaded = $uploaded->update($meta);
                  $uploaded = $uploaded->update(
                    [
                      'content_url' => option('libis.solis-records.solis-baseUrl') . '/_images/' . strtolower($data['name']) . '.' . $data['extension'],
                      'filename_solis' => strtolower($data['name']) . '.' . $data['extension']
                    ]
                  );
                  HookBypass::$skipFileUpdateHook = false;
                }
                else {
                  return [
                    'status' => 'error',
                    'code'   => 500,
                    'message' => t('libis.solis.error.page.of.imageupload')
                  ];
                }
              }
              else {
                return [
                  'status' => 'error',
                  'code'   => 500,
                  'message' => t('libis.solis.error.page.of.imageupload')
                ];
              }
            }
            catch(Exception $e) {
              return [
                'status' => 'error',
                'code'   => $e->getCode(),
                'message'=> $e->getMessage(),
                'file'    => $e->getFile(),
              ];
            }

            try {
              $fileData = array_merge([
                'content_url' => option('libis.solis-records.solis-baseUrl') . '/_images/' . strtolower($data['name']) . '.' . $data['extension'],
              ], $meta);

              $response = postImageObject($fileData);

              if($response['status'] == "success") {
                $result = $response['result'];
                $solisId = $result['data'][0]['id'] ?? '';
                HookBypass::$skipFileUpdateHook = true;
                $uploaded = $uploaded->update(
                  [
                    'solis_id' => $solisId,
                  ]
                );
                HookBypass::$skipFileUpdateHook = false;

                return [
                  'status' => 'success',
                  'code'   => 200,
                  'message' => $response['result']
                ];
              }
              else {
                return [
                  'status' => 'error',
                  'message' => t("libis.solis.error.solis.request.failed") . ' ' . $response['message'] ,
                ];
              }
  
            }
            catch(Exception $e) {
              return [
                'status' => 'error',
                'message' => t("libis.solis.error.solis.request.failed") . ' ' . $e->getMessage(),
              ];
            }
          }
          else {
            return [
              'status' => 'error',
              'code'   => 500,
              'message'=> t('libis.solis.no.rights.add')
            ];

          }
        }
      ],
    ]
  ),
  'translations' => (function () {
		$translations = [];
		$dir = __DIR__ . '/translations';

		foreach (glob($dir . '/*.json') as $file) {
		$lang = basename($file, '.json');
		$json = file_get_contents($file);
		$translations[$lang] = json_decode($json, true);
		}

		return $translations;
	})(),
]);
