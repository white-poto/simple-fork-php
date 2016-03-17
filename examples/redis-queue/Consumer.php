<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2016/3/17
 * Time: 19:57
 */

declare(ticks = 1);
require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Consumer extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        $queue = new \Jenner\SimpleFork\Queue\RedisQueue('127.0.0.1', 6379, 1);
        while (true) {
            $res = $queue->get();
            if ($res !== false) {
                echo $res . PHP_EOL;
            }
            //usleep(100000);
        }
    }
}

$consumer = new Consumer();
$consumer->start();
$consumer->wait();


