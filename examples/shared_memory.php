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
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();
        for ($i = 0; $i < 10; $i++) {
            $cache->set($i, $i);
            echo "set {$i} : {$i}" . PHP_EOL;
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        sleep(5);
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();
        for ($i = 0; $i < 10; $i++) {
            echo "get {$i} : " . $cache->get($i) . PHP_EOL;
        }
    }
}


$producer = new Producer();

$worker = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute($producer);
$pool->execute($worker);
$pool->wait();