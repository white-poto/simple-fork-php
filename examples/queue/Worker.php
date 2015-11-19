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
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue(1, "/tmp/simple-fork-test.ipc");
        while (true) {
            $res = $queue->get();
            if ($res !== false) {
                echo $this->getPid() . ":" . $res . PHP_EOL;
            }
            //usleep(100000);
        }
    }
}


$worker_1 = new Worker();

$worker_2 = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute($worker_1);
$pool->execute($worker_2);

$pool->wait();