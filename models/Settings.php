<?php namespace BeySong\IndexNow\Models;

use Model;
use Flash;
use Redirect;
use BeySong\IndexNow\Classes\IndexNowClient;

class Settings extends Model
{
    /**
     * @var array implement these behaviors
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * @var string settingsCode unique to this model
     */
    public $settingsCode = 'beysong_indexnow_settings';

    /**
     * @var string settingsFields configuration
     */
    public $settingsFields = 'fields.yaml';

    /**
     * Submit sitemap to IndexNow — called via AJAX from settings form
     */
    public function onSubmitNow()
    {
        $apikey = post('BeySong\IndexNow\Models\Settings[apikey]', $this->apikey);
        $submitUrl = post('BeySong\IndexNow\Models\Settings[submit_url]', '');

        if (empty($apikey)) {
            Flash::error('API key is not set. Please configure it first.');
            return Redirect::back();
        }

        $siteUrl = config('app.url', 'https://example.com');
        $sitemapUrl = !empty($submitUrl) ? $submitUrl : rtrim($siteUrl, '/') . '/sitemap.xml';

        $client = new IndexNowClient();
        $result = $client->submit($apikey, $siteUrl, [$sitemapUrl]);

        if ($result['success']) {
            Flash::success($result['message']);
        } else {
            Flash::error($result['message']);
        }

        return Redirect::back();
    }
}
