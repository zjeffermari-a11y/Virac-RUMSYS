<?php

namespace App\Services;

use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\Protobuf\Value;

class GeminiService
{
    protected $client;
    protected $projectId;
    protected $location;
    protected $publisher;
    protected $model;

    public function __construct()
    {
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID', 'your-gcp-project-id'); // Replace with your actual GCP Project ID if needed
        $this->location = env('GEMINI_LOCATION', 'us-central1'); // e.g., us-central1
        $this->publisher = env('GEMINI_PUBLISHER', 'google');
        $this->model = env('GEMINI_MODEL', 'gemini-pro'); // or gemini-pro-vision

        // Ensure the API key is loaded from .env
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            throw new \Exception('GEMINI_API_KEY not set in .env file.');
        }

        $this->client = new PredictionServiceClient([
            'apiEndpoint' => "{$this->location}-aiplatform.googleapis.com",
            'credentials' => [
                'api_key' => $apiKey,
            ],
        ]);
    }

    public function generateText(string $prompt): string
    {
        $endpoint = $this->client->endpointName(
            $this->projectId,
            $this->location,
            $this->publisher,
            $this->model
        );

        $instance = new Value();
        $instance->setStructValue(
            new \Google\Protobuf\Struct([
                'fields' => [
                    'prompt' => new Value(['stringValue' => $prompt]),
                ],
            ])
        );

        $instances = [$instance];

        $predictRequest = new PredictRequest();
        $predictRequest->setEndpoint($endpoint);
        $predictRequest->setInstances($instances);

        $response = $this->client->predict($predictRequest);

        $predictions = $response->getPredictions();
        if (!empty($predictions)) {
            $prediction = $predictions[0];
            // Assuming the text is in a 'content' field within the prediction
            $content = $prediction->getStructValue()->getFields()['content']->getStringValue();
            return $content;
        }

        return 'No prediction found.';
    }

    public function __destruct()
    {
        $this->client->close();
    }
}