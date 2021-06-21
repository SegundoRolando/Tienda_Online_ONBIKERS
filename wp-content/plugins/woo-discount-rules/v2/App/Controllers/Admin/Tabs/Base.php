<?php

namespace Wdr\App\Controllers\Admin\Tabs;

use Wdr\App\Controllers\Base as BaseController;

if (!defined('ABSPATH')) exit;

abstract class Base extends BaseController
{
    public $title = NULL, $priority, $input, $base;
    protected $tab;

    /**
     * Base constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * ajax call
     */
    public function ajax() {
        $method = isset( $_REQUEST['method'] ) ? $_REQUEST['method'] : '';
        $method = "ajax_{$method}";

        if ( method_exists( $this, $method ) ) {
            $this->$method();
        }
    }

    /**
     * render templates
     * @param null $page
     * @return mixed
     */
    abstract function render($page = NULL);


}