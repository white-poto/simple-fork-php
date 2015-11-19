<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:38
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        sleep(10);
        echo getmypid() . ':done' . PHP_EOL;
    }
}

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));



while ($pool->aliveCount() > 0) {
    echo "i am waiting" . PHP_EOL;
    $pool->wait(false);
    sleep(1);
}