<?php
namespace Wdr\App\Compatibility;

use Wdr\App\Controllers\Admin\Tabs\Compatible;

if (!defined('ABSPATH')) exit;

class Base
{
    protected $config;
    public function __construct()
    {
        $this->config = Compatible::getInstance();
    }
}