<?php namespace Beysong\IndexNow\Controllers;

use Backend\Classes\Controller;
use Beysong\IndexNow\Models\Settings;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class IndexNowController extends Controller
{
    public function generateKey()
    {
        $settings = Settings::instance();
        $key = $settings->apikey ?? '';

        if (empty($key)) {
            $key = Str::random(32);
            $settings->apikey = $key;
            $settings->save();
        }

        // Return JSON for AJAX requests, otherwise redirect
        if (request()->wantsJson()) {
            return response()->json(['key' => $key]);
        }

        return Redirect::to(
            Config::get('app.url') . '/backend/system/settings/update/beysong/indexnow'
        );
    }

    public function submit()
    {
        $settings = Settings::instance();
        $apikey = $settings->apikey ?? '';

        if (empty($apikey)) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'API key is not set.'], 400);
            }
            return Redirect::back()->with('flash_error', 'API key is not set.');
        }

        $siteUrl = Config::get('app.url', 'https://example.com');
        $sitemapUrl = !empty($settings->submit_url)
            ? $settings->submit_url
            : rtrim($siteUrl, '/') . '/sitemap.xml';

        $client = new \Beysong\IndexNow\Classes\IndexNowClient();
        $result = $client->submit($apikey, $siteUrl, [$sitemapUrl]);

        if (request()->wantsJson()) {
            if ($result['success']) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            } else {
                return response()->json(['error' => $result['message']], 400);
            }
        }

        if ($result['success']) {
            return Redirect::back()->with('flash_success', $result['message']);
        } else {
            return Redirect::back()->with('flash_error', $result['message']);
        }
    }
}
