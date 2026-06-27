<?php namespace BeySong\IndexNow\Controllers;

use Backend\Classes\Controller;
use BeySong\IndexNow\Models\Settings;
use Flash;
use Redirect;

class SettingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle "Submit Sitemap to IndexNow Now" button
     */
    public function onSubmitNow()
    {
        $settings = Settings::instance();
        $result = $settings->submitToIndexNow();

        if ($result['success']) {
            Flash::success($result['message']);
        } else {
            Flash::error($result['message']);
        }

        return Redirect::back();
    }
}
