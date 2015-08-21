<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/19
 * Time: 14:32
 */

declare(ticks = 1);
require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Producer extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->queue->put(1, $i);
            //usleep(50000);
        }
    }
}

$queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue(1, "/tmp/simple-fork-test.ipc");
$producer = new Producer();
$producer->setQueue($queue);
$producer->start();
$producer->wait();