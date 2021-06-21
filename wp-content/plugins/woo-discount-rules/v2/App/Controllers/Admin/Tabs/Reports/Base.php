<?php

namespace Wdr\App\Controllers\Admin\Tabs\Reports;

use Wdr\App\Controllers\Base as BaseController;

if (!defined('ABSPATH')) exit;

abstract class Base extends BaseController
{
    /**
     * Base constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    abstract function get_subtitle();

    /**
     * @return string
     */
    abstract function get_type();

    /**
     * @param array $params
     *
     * @return array
     */
    abstract function get_data( $params );
}