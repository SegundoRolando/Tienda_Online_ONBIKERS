<?php

namespace Wdr\App\Helpers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Template
{
    private $path, $data = array();

    /**
     * set the file path
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set data for template
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * get the file path
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * return the template contents
     */
    function render()
    {
        return $this->templateContents();
    }

    /**
     * Show the template contents
     */
    function display()
    {
        echo $this->templateContents();
    }

    /**
     * process template contents
     */
    function templateContents()
    {
        ob_start();
        if (file_exists($this->getPath())) {
            extract($this->data);
            include $this->getPath();
        }
        return ob_get_clean();
    }
}