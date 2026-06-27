<?php namespace Beysong\IndexNow\Models;

use System\Models\SettingModel;

class Settings extends SettingModel
{
    public $settingsCode = 'beysong_indexnow_settings';
    public $settingsFields = 'fields.yaml';
}
