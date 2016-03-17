<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2016/3/17
 * Time: 19:53
 */

declare(ticks = 1);
require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Producer extends \Jenner\SimpleFork\Process {
    public function run() {
        $queue = new \Jenner\SimpleFork\Queue\RedisQueue('127.0.0.1', 6379, 1);
        for($i=0; $i<100000; $i++) {
            $queue->put(getmypid() . '-' . mt_rand(0, 1000));
        }
        $queue->close();
    }
}


$pool = new \Jenner\SimpleFork\Pool();
for($i = 0; $i< 10; $i++) {
    $process = new Producer();
    $pool->execute($process);
}


$pool->wait(true);