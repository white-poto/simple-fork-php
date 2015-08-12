<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:54
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable extends \Jenner\SimpleFork\Runnable{

    /**
     * @var \Jenner\SimpleFork\IPC\Semaphore
     */
    protected $sem;

    public function __construct(){
        $this->sem = \Jenner\SimpleFork\IPC\Semaphore::create("test");
    }

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        for($i = 0; $i < 20; $i ++){
            $this->sem->acquire();
            echo "my turn: " . getmypid() . PHP_EOL;
            $this->sem->release();
        }
    }
}

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->start();
$pool->wait();