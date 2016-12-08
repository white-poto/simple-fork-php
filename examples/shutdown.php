<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/13
 * Time: 9:22
 */

declare(ticks = 1);
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * @return void
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
$process->start();
sleep(5);
$process->shutdown();