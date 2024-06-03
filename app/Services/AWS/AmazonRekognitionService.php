<?php

namespace App\Services\AWS;

/*
|----------------------------------------------------------------------------------------------------
| Amazon Rekognition Service
|----------------------------------------------------------------------------------------------------
|
| Amazon Rekognition makes it easy to add image and video analysis to your applications
| You just provide an image or video to the Amazon Rekognition API,and the service can identify:
| - objects, people, text, scenes, activities, any inappropriate content
|
| Amazon Rekognition also provides highly accurate:
| - facial analysis and facial recognition
|
| With Amazon Rekognition Custom Labels, you can create a machine learning model that finds:
| - objects, scenes, and concepts that are specific to your business needs
|
| The configuration options set in to `Aws\Sdk` object will be passed directly to service for $client
|
| The full set of possible options are documented at: https://docs.aws.amazon.com/rekognition/index.html
|
*/
use Aws\Rekognition\RekognitionClient;

class AmazonRekognitionService
{
    /**
     * Configurations
     */
    private mixed $rekognitionClientConfig;

    private mixed $s3Bucket;

    private mixed $minConfidence;

    private RekognitionClient $client;

    public function __construct(?array $rekognitionClientConfig = null, ?string $s3Bucket = null, int $minConfidence = 0)
    {
        $this->rekognitionClientConfig = $rekognitionClientConfig ?? config('aws.rekognition.client');
        $this->s3Bucket = $s3Bucket ?? config('aws.rekognition.bucket');
        $this->minConfidence = $minConfidence ?? config('aws.rekognition.minConfidence');

        $this->client = new RekognitionClient($this->rekognitionClientConfig);
    }

    public function detectFaces(string $s3ObjectKey, string $attributesList = 'ALL'): bool
    {
        try {
            $result = $this->client->detectFaces([
                'Attributes' => [$attributesList],
                'Image' => [
                    // 'Bytes' => $bytes // blob | base64-encoded-bytes
                    'S3Object' => [
                        'Bucket' => $this->s3Bucket,
                        'Name' => $s3ObjectKey,
                    ],
                ],
                'MinConfidence' => $this->minConfidence,
            ])->toArray();

            if ($this->isFacesDetected($result) && ! $this->isMultipleFacesDetected($result['FaceDetails']) && $this->isFullFace($result['FaceDetails'][0])) {
                return $result['FaceDetails'][0];
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isFacesDetected(array $data): bool
    {
        if ($data['@metadata'] && $data['@metadata']['statusCode'] === 200) {
            return count($data['FaceDetails']) > 0;
        }

        return false;
    }

    private function isMultipleFacesDetected(array $faces)
    {
        return count($faces) > 1;
    }

    private function isFullFace(array $faceDetails): bool
    {
        if (! $this->isValidBoundingBox($faceDetails['BoundingBox'])) {
            return false;
        }

        if (! $this->areEyesOpen($faceDetails['EyesOpen'])) {
            return false;
        }

        if (! $this->areValidLandmarks($faceDetails['Landmarks'])) {
            return false;
        }

        return true;
    }

    private function isValidBoundingBox(array $boundingBox): bool
    {
        return min($boundingBox) >= 0;
    }

    private function areEyesOpen(array $eyesOpen): bool
    {
        return $eyesOpen['Value'] && $eyesOpen['Confidence'] >= 80;
    }

    /**
     * Facial analysis and facial recognition
     */
    private function areValidLandmarks(array $landmarks): bool
    {
        foreach ($landmarks as $landmark) {
            if (min($landmark) < 0) {
                return false;
            }
        }

        return true;
    }
}

/**
 * // By uploading image
 * // Subject to later enhance with polymorphism decision
 * $extension = $request->file('file')->extension(); // aws: jpg | png
 * $image = fopen($request->file('file')->getPathName(), 'r'); // aws: base64-encoded bytes | blob
 * $bytes = fread($image, $request->file('file')->getSize());
*/
