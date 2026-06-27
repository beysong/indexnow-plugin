<?php namespace BeySong\IndexNow\Classes;

use Config;
use Http;

class IndexNowClient
{
    /**
     * IndexNow API endpoint
     */
    protected $apiUrl = 'https://api.indexnow.org/Submit';

    /**
     * Submit URLs to IndexNow
     *
     * @param string $apikey
     * @param string $siteUrl
     * @param array $urls
     * @return array
     */
    public function submit(string $apikey, string $siteUrl, array $urls): array
    {
        if (empty($apikey) || empty($siteUrl) || empty($urls)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: apikey, siteUrl, or urls.',
            ];
        }

        $payload = [
            'host'      => rtrim($this->normalizeUrl($siteUrl), '/'),
            'key'       => $apikey,
            'keyLocation' => rtrim($this->normalizeUrl($siteUrl), '/') . '/' . $apikey . '.txt',
            'urlList'   => $urls,
        ];

        try {
            $response = Http::post($this->apiUrl, $payload);

            if ($response->status() == 200) {
                return [
                    'success' => true,
                    'message' => 'Successfully submitted ' . count($urls) . ' URL(s) to IndexNow.',
                ];
            }

            $body = $response->json();
            return [
                'success' => false,
                'message' => 'IndexNow returned status ' . $response->status() . ': ' . ($body['message'] ?? 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to submit to IndexNow: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize URL - remove protocol and trailing slash
     */
    protected function normalizeUrl(string $url): string
    {
        return preg_replace('#^https?://#', '', rtrim($url, '/'));
    }
}
