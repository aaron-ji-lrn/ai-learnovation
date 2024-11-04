<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as Drive;
use Google\Service\Slides;
use Google\Service\Docs;
use Google\Service\Slides\Request as SlidesRequest;
use Google\Service\Docs\Request as DocsRequest;
use Google\Service\Slides\BatchUpdatePresentationRequest as BatchUpdatePresentationRequest;
use Google\Service\Docs\BatchUpdateDocumentRequest;
use Google\Service\Docs\TextStyle;
use Google\Service\Docs\UpdateTextStyleRequest;
use Google\Service\Docs\WeightedFontFamily;
use Google\Service\Drive\DriveFile;
use Parsedown;

class GoogleService
{
    public const GOOGLE_SERVICE_CREDENTIAL_PATH = 'app/google/credentials.json';
    public const GOOGLE_TOKEN_PATH = 'app/google/token.json';
    public const GOOGLE_CALLBACK = 'google.callback';
    public $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(storage_path(self::GOOGLE_SERVICE_CREDENTIAL_PATH)); // Path to your credentials.json
        $this->client->setScopes([
            'https://www.googleapis.com/auth/presentations',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/documents',
            'https://www.googleapis.com/auth/spreadsheets'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setRedirectUri(route(self::GOOGLE_CALLBACK));
    }

    public function authenticate()
    {
        // Load previously authorized token from a file
        $tokenPath = storage_path(self::GOOGLE_TOKEN_PATH);
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
            // Save the token to a file
            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
        } else if ($this->client->isAccessTokenExpired()) {
            // If there is no previous token or it's expired, get a new one
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
            } else {
                $authUrl = $this->client->createAuthUrl();
                return redirect($authUrl);
            }
        }

        return $this->client;
    }

    public function addTicketsToSlide($client, $tickets)
    {
        $deliveredTextContent = 'Delivered:'. "\n" . $this->buildTextContent($tickets['delivered']);
        $inProgressTextContent = 'In progress:'. "\n" . $this->buildTextContent($tickets['in_progress']);
        $this->addTextToSlide($client, $deliveredTextContent);
        $this->addTextToSlide($client, $inProgressTextContent);
    }

    public function addTextToSlide($client, $text)
    {
        $presentationId = env('GOOGLE_SLIDE_ID');
        $pageObjectId = env('GOOGLE_SLIDE_PAGE_ID');
        if (!$presentationId || !$pageObjectId) {
            return ['error' => 'Google Slide ID or Page ID not found'];
        }

        $slidesService = new Slides($client);

        // Generate a unique ID for the text box
        $textBoxId = 'text_box_' . uniqid();

        // Create a request to insert a text box into the slide
        $requests = [
            new SlidesRequest([
                'createShape' => [
                    'objectId' => $textBoxId,
                    'shapeType' => 'TEXT_BOX',
                    'elementProperties' => [
                        'pageObjectId' => $pageObjectId,
                        'size' => [
                            'height' => [
                                'magnitude' => 4000000, // Adjust height (in EMU)
                                'unit' => 'EMU',
                            ],
                            'width' => [
                                'magnitude' => 9000000, // Adjust width (in EMU)
                                'unit' => 'EMU',
                            ],
                        ],
                        'transform' => [
                            'scaleX' => 1,
                            'scaleY' => 1,
                            'translateX' => 100000, // Adjust position on slide
                            'translateY' => 1000000,
                            'unit' => 'EMU',
                        ],
                    ],
                ],
            ]),
            // Insert text into the text box
            new SlidesRequest([
                'insertText' => [
                    'objectId' => $textBoxId,
                    'insertionIndex' => 0,
                    'text' => $text,
                ],
            ]),
            new SlidesRequest([
                'updateTextStyle' => [
                    'objectId' => $textBoxId,
                    'style' => [
                        'bold' => false,
                        'fontSize' => [
                            'magnitude' => 12,
                            'unit' => 'PT',
                        ],
                    ],
                    'fields' => 'bold,fontSize',
                ],
            ]),
            new SlidesRequest([
                'createParagraphBullets' => [
                    'objectId' => $textBoxId,
                    'textRange' => [
                        'type' => 'ALL'
                    ],
                    'bulletPreset' => 'BULLET_DISC_CIRCLE_SQUARE' // First level bullet style
                ]
            ])
        ];

        // Execute the requests
        $batchUpdateResponse = $slidesService->presentations->batchUpdate($presentationId, new BatchUpdatePresentationRequest([
            'requests' => $requests,
        ]));
    }

    protected function buildTextContent($data)
    {
        $text = '';

        foreach ($data as $codebase => $tickets) {
            $text .= "{$codebase}\n\t";
            $lastTicket = array_pop($tickets);
            foreach ($tickets as $ticket) {
                $text .= "{$ticket['summary']}\n\t";
            }
            $text .= "{$lastTicket['summary']}\n";
        }

        return $text;
    }

    public function uploadVideoToDrive($client, $fileName)
    {
        $mediaFolder = env('MEDIA_FOLDER');
        $googleDriveFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
        if (!$googleDriveFolderId) {
            return response()->json(['error' => 'Google Drive folder ID not found'], 500);
        }

        $driveService = new Drive($client);
        // Create a new file metadata
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$googleDriveFolderId], // Replace with the folder ID where you want to upload the file
            'mimeType' => 'video/mp4' // Update based on your audio format if necessary
        ]);

        // Read the file content
        $filePath = public_path($mediaFolder . '/' .$fileName);
        $content = file_get_contents($filePath);

        // Upload the file
        $file = $driveService->files->create($fileMetadata, [
            'data' => $content,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        // Return response
        $data = [
            'message' => 'File uploaded successfully!',
            'file_id' => $file->id
        ];
        return $data;
    }

    public function insertVideoToSlide($client, $videoFileId)
    {
        $presentationId = env('GOOGLE_SLIDE_ID');
        $pageObjectId = env('GOOGLE_SLIDE_PAGE_ID');

        if (!$presentationId || !$pageObjectId) {
            return response()->json(['error' => 'Google Slide ID or Page ID not found'], 500);
        }

        $slidesService = new Slides($client);

        $requests = [
            new SlidesRequest([
                'createVideo' => [
                    'source' => 'DRIVE', // Specifies Google Drive as the source
                    'id' => $videoFileId, // Google Drive file ID
                    'elementProperties' => [
                        'pageObjectId' => $pageObjectId,
                        'size' => [
                            'height' => [
                                'magnitude' => 3000000, // Example size in EMU units
                                'unit' => 'EMU'
                            ],
                            'width' => [
                                'magnitude' => 5000000, // Example size in EMU units
                                'unit' => 'EMU'
                            ]
                        ],
                        'transform' => [
                            'scaleX' => 0.3,
                            'scaleY' => 0.3,
                            'translateX' => 7600000, // Position on the slide
                            'translateY' => 4200000, // Position on the slide
                            'unit' => 'EMU'
                        ]
                    ]
                ]
            ])
        ];

        // 4. Execute the request
        $batchUpdateResponse = $slidesService->presentations->batchUpdate($presentationId, new BatchUpdatePresentationRequest([
            'requests' => $requests,
        ]));

        return $batchUpdateResponse;
    }

    // Function to insert content into another document
    public function insertContentIntoDocument($client, $targetDocumentId, $templateDocumentId)
    {
        $docsService = new Docs($client);
        $content = $this->getDocumentContent($client, $templateDocumentId);
        $requests = [];

        $insertIndex = 1;
        
        // Iterate over the content and insert it into the target document
        foreach ($content as $item) {
            if (isset($item['sectionBreak'])) {
                $requests[] = new DocsRequest([
                    'insertSectionBreak' => [
                        'location' => ['index' => $item['endIndex']],
                        'sectionType' => 'NEXT_PAGE'
                        // 'sectionStyle' => $item['sectionBreak']['sectionStyle']
                    ]
                ]);
            }

            if (isset($item['paragraph'])) {
                $elements = $item['paragraph']['elements'];
                $paragraphContent = '';

                // Concatenate text from elements
                foreach ($elements as $element) {
                    $paragraphContent .= $element['textRun']['content'];
                }

                $requests[] = new DocsRequest([
                    'insertText' => [
                        'text' => $paragraphContent,
                        'location' => ['index' => $item['startIndex']]
                    ]
                ]);

                // Apply text styles
                $startIndex = $item['startIndex'];
                foreach ($elements as $element) {
                    if (isset($element['textRun']['textStyle'])) {
                        $textStyle = $element['textRun']['textStyle'];

                        $validTextStyle = [];

                        // Filter out only valid fields
                        foreach ($textStyle as $key => $value) {
                            if (in_array($key, ['bold', 'italic', 'underline', 'foregroundColor', 'fontSize', 'weightedFontFamily'])) {
                                $validTextStyle[$key] = $value;
                            }
                        }
                        // print_r($textStyle);
                        $requests[] = new DocsRequest([
                            'updateTextStyle' => [
                                'textStyle' => $textStyle,
                                'range' => [
                                    'startIndex' => $startIndex,
                                    'endIndex' => $startIndex + strlen($element['textRun']['content'])
                                ],
                                'fields' => implode(',', array_keys((array)$validTextStyle))
                            ]
                        ]);
                    }
                    $startIndex += strlen($element['textRun']['content']);
                }

                // Apply paragraph style
                if (isset($item['paragraph']['paragraphStyle'])) {
                    $paragraphStyle = $item['paragraph']['paragraphStyle'];
                    $validParagraphStyle = [];

                    // Filter out only valid fields
                    foreach ($paragraphStyle as $key => $value) {
                        if (in_array($key, ['alignment', 'lineSpacing', 'spacingMode', 'indentStart', 'indentEnd', 'indentFirstLine'])) {
                            $validParagraphStyle[$key] = $value;
                        }
                    }
                    $requests[] = new DocsRequest([
                        'updateParagraphStyle' => [
                            'paragraphStyle' => $paragraphStyle,
                            'range' => [
                                'startIndex' => $item['startIndex'],
                                'endIndex' => $item['endIndex']
                            ],
                            'fields' => '*'
                        ]
                    ]);
                }
            }
        }

        // Perform the batch update request
        $batchUpdateRequest = new BatchUpdateDocumentRequest([
            'requests' => $requests
        ]);

        $docsService->documents->batchUpdate($targetDocumentId, $batchUpdateRequest);
    }

    function textStyleToArray(TextStyle $textStyle) {
        $array = [];
    
        // Convert boolean properties
        $array['bold'] = $textStyle->getBold() ?? false;
        $array['italic'] = $textStyle->getItalic() ?? false;
        $array['underline'] = $textStyle->getUnderline() ?? false;
        $array['strikethrough'] = $textStyle->getStrikethrough() ?? false;
        $array['smallCaps'] = $textStyle->getSmallCaps() ?? false;
    
        // Convert weightedFontFamily if it exists
        if ($weightedFontFamily = $textStyle->getWeightedFontFamily()) {
            $array['weightedFontFamily'] = [
                'fontFamily' => $weightedFontFamily->getFontFamily(),
                'weight' => $weightedFontFamily->getWeight(),
            ];
        }
    
        return $array;
    }
    public function getDocumentContent($client, $documentId)
    {
        $docsService = new Docs($client);
        $doc = $docsService->documents->get($documentId);
        return $doc->getBody()->getContent(); // Get the content (structural elements)
    }

}
