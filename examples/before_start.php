<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 14:37
 */

declare(ticks = 1);
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        while (true) {
            echo "I am running" . PHP_EOL;
            sleep(1);
        }
    }

}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->on(\Jenner\SimpleFork\Process::BEFORE_START, function () {
    echo "start" . PHP_EOL;
    return true;
});

$process->start();
sleep(5);
$process->shutdown();