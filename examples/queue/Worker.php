<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/19
 * Time: 14:32
 */

declare(ticks = 1);
require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

error_reporting(E_ALL);

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        while (true) {
            $res = $this->queue->get(1);
            if ($res !== false) {
                echo $this->getPid() . ":" . $res . PHP_EOL;
            }
            //usleep(100000);
        }
    }
}

$queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue(1, "/tmp/simple-fork-test.ipc");

$worker_1 = new Worker();
$worker_1->setQueue($queue);

$worker_2 = new Worker();
$worker_2->setQueue($queue);

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($worker_1);
$pool->submit($worker_2);

$pool->start();
$pool->wait();