<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/13
 * Time: 11:28
 */

error_reporting(E_ALL);

spl_autoload_register(function ($classname) {
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    if (stristr($classname, "\\Jenner\\SimpleFork\\") == 0) {
        $file = $dir . basename(str_replace('\\', '/', $classname));
        if (file_exists($file)) require $file . '.php';
    }
});

$pool = new \Jenner\SimpleFork\Pool();