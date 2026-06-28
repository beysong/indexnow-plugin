<?php namespace Beysong\IndexNow\Classes;

use Config;
use Http;
use SimpleXMLElement;

class IndexNowClient
{
    /**
     * IndexNow API endpoint
     */
    protected $apiUrl = 'https://api.indexnow.org/IndexNow';

    /**
     * Submit URLs to IndexNow
     *
     * @param string $apikey
     * @param string $siteUrl
     * @param array $urls  First URL should be the sitemap URL
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

        // Fetch sitemap and extract all URLs
        $sitemapUrl = $urls[0];
        $allUrls = $this->extractUrlsFromSitemap($sitemapUrl);

        if (empty($allUrls)) {
            return [
                'success' => false,
                'message' => 'No URLs found in sitemap.',
            ];
        }

        $payload = [
            'host'        => $host,
            'key'         => $apikey,
            'keyLocation' => $siteUrl . '/' . $apikey . '.txt',
            'urlList'     => $allUrls,
        ];

        try {
            $response = Http::post($this->apiUrl, $payload);
            $status = $response->status();
            $body = $response->body();

            if ($status == 202) {
                return [
                    'success' => true,
                    'message' => 'Successfully submitted ' . count($allUrls) . ' URL(s) to IndexNow.',
                ];
            }

            $json = $response->json();
            return [
                'success' => false,
                'message' => 'IndexNow returned status ' . $status . ': ' . ($json['message'] ?? $body),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to submit to IndexNow: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch sitemap XML and extract all URLs from <loc> tags
     *
     * @param string $sitemapUrl
     * @return array
     */
    protected function extractUrlsFromSitemap(string $sitemapUrl): array
    {
        try {
            $response = Http::timeout(10)->get($sitemapUrl);

            if ($response->status() !== 200) {
                return [];
            }

            $xml = @simplexml_load_string($response->body());

            if (!$xml) {
                return [];
            }

            $urls = [];

            // Handle <url> elements inside <urlset>
            foreach ($xml->url as $url) {
                if (isset($url->loc) && !empty((string) $url->loc)) {
                    $loc = trim((string) $url->loc);
                    if (filter_var($loc, FILTER_VALIDATE_URL)) {
                        $urls[] = $loc;
                    }
                }
            }

            // Handle <sitemap> elements inside <sitemapindex> (sitemap of sitemaps)
            foreach ($xml->sitemap as $sitemap) {
                if (isset($sitemap->loc) && !empty((string) $sitemap->loc)) {
                    $loc = trim((string) $sitemap->loc);
                    // Recursively fetch child sitemaps
                    $childUrls = $this->extractUrlsFromSitemap($loc);
                    $urls = array_merge($urls, $childUrls);
                }
            }

            return array_unique($urls);
        } catch (\Exception $e) {
            return [];
        }
    }
}
