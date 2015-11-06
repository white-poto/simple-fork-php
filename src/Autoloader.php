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
    /**
     * @var string name space prefix
     */
    protected $prefix;

    /**
     * @var string file base path
     */
    protected $path;

    /**
     * @param string $path
     */
    protected function __construct($path = __DIR__)
    {
        $this->path = $path;
        $this->prefix = __NAMESPACE__ . '\\';
        $this->prefix_length = strlen($this->prefix);
    }

    /**
     * register simple-fork autoload
     */
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
        $filename = $this->path . DIRECTORY_SEPARATOR . $short_name . '.php';
        if (file_exists($filename)) {
            require $filename;
        }
    }
}