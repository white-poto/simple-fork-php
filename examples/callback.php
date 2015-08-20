<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 12:11
 */


declare(ticks = 1);
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$process = new \Jenner\SimpleFork\Process(function () {
    for ($i = 0; $i < 3; $i++) {
        echo $i . PHP_EOL;
        sleep(1);
    }
});

$process->start();
$process->wait();