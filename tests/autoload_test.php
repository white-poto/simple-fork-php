<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/13
 * Time: 11:28
 */

spl_autoload_register(function ($classname) {
    echo $classname . PHP_EOL;
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    echo $dir . PHP_EOL;
    if (stristr($classname, "\\Jenner\\SimpleFork\\") == 0) {
        $file = $dir . basename($classname);
        echo $file . PHP_EOL;
        if (file_exists($file)) include $file;
    }
});

$pool = new \Jenner\SimpleFork\Pool();