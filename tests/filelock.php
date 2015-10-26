<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 15:50
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$lock_file = "/tmp/simple-fork.lock";
if (!file_exists($lock_file)) {
    touch($lock_file);
}
$process = new \Jenner\SimpleFork\Process(function () use ($lock_file) {
    $lock = \Jenner\SimpleFork\Lock\FileLock::create($lock_file);
    echo getmypid() . PHP_EOL;
    var_dump($lock->acquire());
    sleep(5);
    echo getmypid() . PHP_EOL;
    var_dump($lock->release());
});
$process->start();
sleep(3);
$lock = \Jenner\SimpleFork\Lock\FileLock::create($lock_file);
echo getmypid() . PHP_EOL;
var_dump($lock->acquire());
$process->wait();
echo getmypid() . PHP_EOL;
var_dump($lock->acquire());
echo getmypid() . PHP_EOL;
var_dump($lock->release());