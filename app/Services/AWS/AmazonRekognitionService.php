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

class AmazonRekognitionService {

    /**
     * Configurations
     * @var $rekognitionClientConfig
     * @var $s3Bucket
     * @var $minConfidence
     *
    */
    private mixed $rekognitionClientConfig;
    private mixed $s3Bucket;
    private mixed $minConfidence;

    private RekognitionClient $client;

    /**
     * The client constructor accepts the following options:
     * @param array|null $rekognitionClientConfig [region, version]
     * @param string|null $s3Bucket
     * @param int $minConfidence
     */
    public function __construct(array $rekognitionClientConfig = null, string $s3Bucket = null, int $minConfidence = 0)
    {
        $this->rekognitionClientConfig = $rekognitionClientConfig ?? config('aws.rekognition.client');
        $this->s3Bucket = $s3Bucket ?? config('aws.rekognition.bucket');
        $this->minConfidence = $minConfidence ?? config('aws.rekognition.minConfidence');

        $this->client = new RekognitionClient($this->rekognitionClientConfig);
    }

    /**
     * @param string $s3ObjectKey
     * @param string $attributesList (ALL || DEFAULT)
     * @return boolean
    */
    public function detectFaces(string $s3ObjectKey, string $attributesList = 'ALL')
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
                'MinConfidence' => $this->minConfidence
            ])->toArray();

            if($this->isFacesDetected($result) && !$this->isMultipleFacesDetected($result['FaceDetails']) && $this->isFullFace($result['FaceDetails'][0]))
                return $result['FaceDetails'][0];

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Aws\Result
     * @param array $data
     * @return boolean
    */
    private function isFacesDetected(array $data)
    {
        if($data['@metadata'] && $data['@metadata']['statusCode'] === 200)
            return count($data['FaceDetails']) > 0;

        return false;
    }

    /**
     * Aws Detect Faces
     * @param array $faces
     * @return boolean
    */
    private function isMultipleFacesDetected(array $faces)
    {
        return count($faces) > 1;
    }

    /**
     * Detected Face
     * @param array $faceDetails
     * @return boolean
    */
    private function isFullFace(array $faceDetails)
    {
        if(!$this->isValidBoundingBox($faceDetails['BoundingBox']))
            return false;

        if(!$this->areEyesOpen($faceDetails['EyesOpen']))
            return false;

        if(!$this->areValidLandmarks($faceDetails['Landmarks']))
            return false;

        return true;
    }

    /**
     * @param array $boundingBox
     * @return boolean
    */
    private function isValidBoundingBox(array $boundingBox)
    {
        return min($boundingBox) >= 0;
    }

    /**
     * @param array $eyesOpen
     * @return boolean
    */
    private function areEyesOpen(array $eyesOpen)
    {
        return $eyesOpen['Value'] && $eyesOpen['Confidence'] >= 80;
    }

    /**
     * Facial analysis and facial recognition
     * @param array $landmarks
     * @return boolean
    */
    private function areValidLandmarks(array $landmarks)
    {
        foreach ($landmarks as $landmark) {
			if(min($landmark) < 0)
                return false;
        };

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
