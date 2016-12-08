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
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
        for ($i = 0; $i < 10; $i++) {
            echo getmypid() . PHP_EOL;
            $queue->put($i);
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        sleep(5);
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
        for ($i = 0; $i < 10; $i++) {
            $res = $queue->get();
            echo getmypid() . ' = ' . $i . PHP_EOL;
            var_dump($res);
        }
    }
}


$producer = new Producer();


$worker = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute($producer);
$pool->execute($worker);
$pool->wait();