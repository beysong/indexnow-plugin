<?php namespace Beysong\IndexNow;

use Route;
use Config;
use Str;
use Redirect;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Beysong\IndexNow\Models\Settings;
use Beysong\IndexNow\Controllers\IndexNowController;

/**
 * IndexNow Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'IndexNow',
            'description' => 'Submit your sitemap to IndexNow (Bing) for fast indexing.',
            'author'      => 'Beysong',
            'icon'        => 'icon-rocket',
            'icon-svg'    => '/plugins/beysong/indexnow/assets/images/plugin-icon.svg',
            'homepage'    => 'https://www.bing.com/indexnow',
            'details'     => 'Submit your sitemap to IndexNow (Bing) for fast indexing. Supports API key verification file generation and manual sitemap submission.',
            'keywords'    => 'indexnow, bing, seo, sitemap, search engine, indexing, fast indexing',
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        // Serve verification file: GET /{apikey}.txt
        Route::get('{apikey}.txt', function ($apikey) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $apikey)) {
                return response('Not Found', 404);
            }

            $settings = Settings::instance();
            $storedKey = $settings->apikey ?? '';

            if ($apikey === $storedKey && !empty($storedKey)) {
                return response($storedKey, 200, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            return response('Not Found', 404);
        })->where('apikey', '[a-zA-Z0-9]+');

        // Key generation
        Route::get('beysong_indexnow/generate-key', [IndexNowController::class, 'generateKey']);

        // IndexNow submission
        Route::get('beysong_indexnow/submit', [IndexNowController::class, 'submit']);
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'beysong.indexnow.manage_settings' => [
                'tab'   => 'IndexNow',
                'label' => 'Manage IndexNow settings'
            ],
        ];
    }

    /**
     * Register scheduled tasks.
     */
    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            $settings = Settings::instance();
            if (!$settings->auto_submit) return;

            $freq = $settings->frequency ?? 'daily';
            $lastRun = \Cache::get('beysong_indexnow_last_run');

            $shouldRun = false;
            if ($freq === 'hourly' && (!$lastRun || now()->diffInMinutes($lastRun) >= 60)) $shouldRun = true;
            if ($freq === 'daily' && (!$lastRun || now()->diffInHours($lastRun) >= 24)) $shouldRun = true;
            if ($freq === 'weekly' && (!$lastRun || now()->diffInDays($lastRun) >= 7)) $shouldRun = true;
            if ($freq === 'monthly' && (!$lastRun || now()->diffInDays($lastRun) >= 30)) $shouldRun = true;

            if (!$shouldRun) return;

            $apikey = $settings->apikey ?? '';
            if (empty($apikey)) return;

            $siteUrl = Config::get('app.url', 'https://example.com');
            $sitemapUrl = !empty($settings->submit_url)
                ? $settings->submit_url
                : rtrim($siteUrl, '/') . '/sitemap.xml';

            $client = new \Beysong\IndexNow\Classes\IndexNowClient();
            $result = $client->submit($apikey, $siteUrl, [$sitemapUrl]);

            if ($result['success']) {
                \Cache::put('beysong_indexnow_last_run', now(), now()->addDays(30));
            }
        })->hourly();
    }

    /**
     * Registers any settings for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'indexnow' => [
                'label'       => 'IndexNow',
                'description' => 'Configure IndexNow API key and auto-submit settings.',
                'category'    => SettingsManager::CATEGORY_CMS,
                'icon'        => 'icon-rocket',
                'class'       => \Beysong\IndexNow\Models\Settings::class,
                'order'       => 900,
                'keywords'    => 'indexnow bing seo sitemap',
                'permissions' => ['beysong.indexnow::manage_settings',],
            ],
        ];
    }
}
