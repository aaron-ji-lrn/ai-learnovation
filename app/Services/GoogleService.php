<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as Drive;
use Google\Service\Slides;
use Google\Service\Slides\Request as SlidesRequest;
use Google\Service\Slides\BatchUpdatePresentationRequest as BatchUpdatePresentationRequest;
use Google\Service\Drive\DriveFile;

class GoogleService
{
    public const GOOGLE_SERVICE_CREDENTIAL_PATH = 'app/google/credentials.json';
    public const GOOGLE_DRIVE_TOKEN_PATH = 'app/google/token_drive.json';
    public const GOOGLE_SLIDES_TOKEN_PATH = 'app/google/token_slides.json';
    protected $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(storage_path(self::GOOGLE_SERVICE_CREDENTIAL_PATH)); // Path to your credentials.json
        $this->client->setScopes([
            'https://www.googleapis.com/auth/presentations',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.readonly'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function authenticate($serviceTokenPath, $callbackRoute)
    {
        $this->client->setRedirectUri(route($callbackRoute));
        // Load previously authorized token from a file
        $tokenPath = storage_path($serviceTokenPath);
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

    public function handleCallback($authCode, $tokenPath)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            
        $this->client->setAccessToken($accessToken);

        // Save the token for future use
        $tokenPath = storage_path($tokenPath);
        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
    }

    public function addTicketsToSlide($tickets, $presentationId, $pageObjectId)
    {
        $callbackRoute = 'google.slides.callback';
        $tokenPath = self::GOOGLE_SLIDES_TOKEN_PATH;
        $authClient = $this->authenticate($tokenPath, $callbackRoute);

        if ($authClient instanceof \Illuminate\Http\RedirectResponse) {
            return $authClient;
        }

        $deliveredTextContent = $this->buildTextContent($tickets['delivered']);
        $inProgressTextContent = $this->buildTextContent($tickets['in_progress']);
        $this->addTextToSlide($deliveredTextContent, $presentationId, $pageObjectId);
        $this->addTextToSlide($inProgressTextContent, $presentationId, $pageObjectId);
    }

    public function addTextToSlide($text, $presentationId, $pageObjectId)
    {
        $slidesService = new Slides($this->client);

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

    public function uploadVideoToDrive($fileName, $googleDriveFolderId)
    {
        $callbackRoute = 'google.drive.callback';
        $tokenPath = self::GOOGLE_DRIVE_TOKEN_PATH;
        $authClient = $this->authenticate($tokenPath, $callbackRoute);

        if ($authClient instanceof \Illuminate\Http\RedirectResponse) {
            return $authClient;
        }

        $driveService = new Drive($this->client);
        // Create a new file metadata
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$googleDriveFolderId], // Replace with the folder ID where you want to upload the file
            'mimeType' => 'video/mp4' // Update based on your audio format if necessary
        ]);

        // Read the file content
        $filePath = public_path($fileName);
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

    public function insertVideoToSlide($videoFileId, $presentationId, $pageObjectId)
    {
        $callbackRoute = 'google.slides.callback';
        $tokenPath = self::GOOGLE_SLIDES_TOKEN_PATH;
        $authClient = $this->authenticate($tokenPath, $callbackRoute);

        if ($authClient instanceof \Illuminate\Http\RedirectResponse) {
            return $authClient;
        }

        $slidesService = new Slides($this->client);

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
}
