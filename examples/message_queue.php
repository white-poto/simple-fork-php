<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:24
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class Producer extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            echo getmypid() . PHP_EOL;
            $this->queue->put(1, $i);
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        sleep(5);
        for ($i = 0; $i < 10; $i++) {
            $res = $this->queue->get(1);
            echo getmypid() . ' = ' . $i . PHP_EOL;
            var_dump($res);
        }
    }
}

$queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
$producer = new Producer();
$producer->setQueue($queue);

$worker = new Worker();
$worker->setQueue($queue);

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();