<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/19
 * Time: 14:32
 */

class Worker extends \Jenner\SimpleFork\Process
{
    public function start(){
        for($i = 0; $i<100; $i++){
            $res = $this->queue->get(1);
            if($res !== false){
                echo $res . PHP_EOL;
            }
            sleep(1);
        }
    }
}

$queue = new \Jenner\SimpleFork\IPC\SystemVMessageQueue(1, "/tmp/simple-fork-test.ipc");
$worker = new Worker();
$worker->setQueue($queue);
$worker->start();