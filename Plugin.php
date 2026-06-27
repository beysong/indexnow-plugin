<?php namespace BeySong\IndexNow;

use Route;
use Config;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use BeySong\IndexNow\Models\Settings;

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
            'description' => 'Submit your sitemap to IndexNow ( Bing ) for fast indexing.',
            'author'      => 'BeySong',
            'icon'        => 'icon-rocket',
            'icon-svg'    => '/plugins/beysong/indexnow/assets/images/plugin-icon.svg',
            'homepage'    => 'https://www.bing.com/indexnow',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        // Register route to serve the verification file: /{apikey}.txt
        Route::get('{apikey}.txt', function ($apikey) {
            $settings = Settings::instance();
            $storedKey = $settings->apikey ?? '';

            if ($apikey === $storedKey) {
                return response($storedKey, 200, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            return response('Not Found', 404);
        })->where('apikey', '[a-z0-9]+');
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
                'class'       => \BeySong\IndexNow\Models\Settings::class,
                'order'       => 900,
                'keywords'    => 'indexnow bing seo sitemap',
                'permissions' => ['beysong.indexnow::manage_settings',],
            ],
        ];
    }
}
