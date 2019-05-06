<?php
namespace App\Service;

class SettingsService
{
    private $db;
    private $settings;

    public function __construct($db)
    {
        $this->db = $db;
        $this->settings = $db->table('options')->where('autoload', 'true')->get()->keyBy('key');
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
