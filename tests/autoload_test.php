<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/13
 * Time: 11:28
 */

error_reporting(E_ALL);

spl_autoload_register(function ($classname) {
    echo $classname . PHP_EOL;
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    echo $dir . PHP_EOL;
    if (stristr($classname, "\\Jenner\\SimpleFork\\") == 0) {
        $file = $dir . basename($classname);
        echo basename($classname) . PHP_EOL;
        echo $file . PHP_EOL;
        if (file_exists($file)) require $file;
    }
});

$pool = new \Jenner\SimpleFork\Pool();