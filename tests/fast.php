<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 16:48
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$pool = new \Jenner\SimpleFork\Pool();
for ($i = 0; $i < 100; $i++) {
    $process = new \Jenner\SimpleFork\Process(function () {
        sleep(30);
        echo getmypid() . PHP_EOL;
    });
    $pool->execute($process);
}
$pool->wait();