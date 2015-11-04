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
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue(1, "/tmp/simple-fork-test.ipc");
        for ($i = 0; $i < 100; $i++) {
            $queue->put($i);
            //usleep(50000);
        }
    }
}


$producer = new Producer();
$producer->start();
$producer->wait();