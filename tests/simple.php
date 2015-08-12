<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 19:09
 */
echo dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();

class TestRunnable extends \Jenner\SimpleFork\Runnable{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        echo "test" . PHP_EOL;
    }
}