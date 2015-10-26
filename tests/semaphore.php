<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 15:49
 */
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$process = new \Jenner\SimpleFork\Process(function () {
    $lock = \Jenner\SimpleFork\Lock\Semaphore::create('test');
    var_dump($lock->acquire());
    sleep(5);
    var_dump($lock->release());
});
$process->start();
sleep(3);
$lock = \Jenner\SimpleFork\Lock\Semaphore::create("test");
$this->assertFalse($lock->acquire());
$process->wait();
$this->assertTrue($lock->acquire());
$this->assertTrue($lock->release());