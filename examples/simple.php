<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 19:09
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * @return void
     */
    public function run()
    {
        echo "I am a sub process" . PHP_EOL;
    }
}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();
$process->wait();