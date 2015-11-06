<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/6
 * Time: 14:26
 */

namespace Jenner\SimpleFork;


class Autoloader
{

    protected $prefix;

    protected $path;

    protected function __construct($path = __DIR__)
    {
        $this->path = $path;
        $this->prefix = __NAMESPACE__ . '\\';
        $this->prefix_length = strlen($this->prefix_length);
    }

    public static function register()
    {
        spl_autoload_register(array(new self(), 'autoload'));
    }

    /**
     * @param $class_name
     */
    protected function autoload($class_name)
    {
        if (0 !== strpos($class_name, $this->prefix)) {
            return;
        }
        $short_name = str_replace('\\', DIRECTORY_SEPARATOR, substr($class_name, $this->prefix_length));
        $filename = $this->path . DIRECTORY_SEPARATOR . $short_name;
        if (file_exists($filename)) {
            require $filename;
        }
    }
}