<?php

namespace App\Services;

class MediaService
{
    public function convertAudioToMp4($audioFileName, $mediaFolder)
    {
        $data = [];
        // Path to your audio file in the public folder
        $imagePath = public_path($mediaFolder . '/cover.jpg');
        $audioPath = public_path($audioFileName);
        $fileName = $mediaFolder . '/sprint_update_'.date('Y-m-d'). '.mp4';
        $outputVideoPath = public_path($fileName);

        try {
            // Check if you have a background image to use as a static image in the video
            if (file_exists($audioPath)) {
                // Escape shell arguments
                $imagePathEscaped = escapeshellarg($imagePath);
                $audioPathEscaped = escapeshellarg($audioPath);
                $outputVideoPathEscaped = escapeshellarg($outputVideoPath);

                // Verify the duration of the input audio file
                $audioDurationCommand = "ffprobe -i $audioPathEscaped -show_entries format=duration -v quiet -of csv=\"p=0\"";
                exec($audioDurationCommand, $audioDurationOutput, $audioDurationReturnVar);
                $audioDuration = floatval($audioDurationOutput[0]);
                $extraInfo[] = 'Input audio duration: ' . $audioDuration . ' seconds';

                // Convert the audio file to a video file with a static image using FFmpeg
                $ffmpegCommand = "ffmpeg -i $audioPathEscaped -c:a copy $outputVideoPathEscaped";
                if ($imagePathEscaped) {
                    $ffmpegCommand = "ffmpeg -loop 1 -i $imagePathEscaped -i $audioPathEscaped -c:a copy -c:v libx264 -shortest $outputVideoPathEscaped";
                }
                
                exec($ffmpegCommand, $output, $returnVar);

                // Debugging information
                $extraInfo[] = 'FFmpeg command: ' . $ffmpegCommand;
                $extraInfo[] = 'FFmpeg output: ' . implode("\n", $output);
                $extraInfo[] = 'FFmpeg return value: ' . $returnVar;

                if ($returnVar !== 0) {
                    // Handle the error
                    $data = [
                        'success' => false,
                        'error' => 'Failed to create video with FFmpeg',
                        'details' => implode("\n", $output),
                    ];
                } else {
                    // Handle the successful conversion
                    $data = [
                        'success' => true,
                        'video' => $fileName,
                    ];
                }
                $data['extra_info'] = $extraInfo;
            } else {
                // Handle the case where the audio file does not exist
                $data = [
                    'success' => false,
                    'error' => 'Audio file not found',
                ];
            }
        } catch (\FFMpeg\Exception\RuntimeException $e) {
            // Log the error message
            $data = [
                'success' => false,
                'error' => 'Failed to create video with FFmpeg',
                'details' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            // Log any other exceptions
            $data = [
                'success' => false,
                'error' => 'An error occurred',
                'details' => $e->getMessage(),
            ];
        }

        return $data;
    }
}
