<?php namespace Beysong\IndexNow\Classes;

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

        $siteUrl = rtrim($siteUrl, '/');
        $host = preg_replace('#^https?://#', '', $siteUrl);

        $payload = [
            'host'        => $host,
            'key'         => $apikey,
            'keyLocation'  => $siteUrl . '/' . $apikey . '.txt',
            'urlList'     => array_map(fn($url) => rtrim($url, '/'), $urls),
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
}
