<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:15
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


class Producer extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->cache->set($i, $i);
            echo "set {$i} : {$i}" . PHP_EOL;
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        sleep(5);
        for ($i = 0; $i < 10; $i++) {
            echo "get {$i} : " . $this->cache->get($i) . PHP_EOL;
        }
    }
}

$memory = new \Jenner\SimpleFork\Cache\SharedMemory();
$producer = new Producer();
$producer->setCache($memory);

$worker = new Worker();
$worker->setCache($memory);

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();