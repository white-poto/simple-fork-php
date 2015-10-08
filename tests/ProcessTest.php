<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/8
 * Time: 16:45
 */
class ProcessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Jenner\SimpleFork\Process
     */
    protected $process_thread;
    /**
     * @var \Jenner\SimpleFork\Process
     */
    protected $process_runable;
    /**
     * @var \Jenner\SimpleFork\Process
     */
    protected $process_callback;

    public function setUp(){
        $this->process_thread = new MyThread();
        $this->process_runable = new \Jenner\SimpleFork\Process(new MyRunnable());
        $this->process_callback = new \Jenner\SimpleFork\Process(function(){
            for($i=0; $i<3; $i++){
                echo "callback pid:" . getmypid() . PHP_EOL;
            }
        });
    }

    public function testWait(){
        $this->process_thread->start();
        $this->process_thread->wait();
        $this->assertEquals(0, $this->process_thread->exitCode());
        $this->assertEquals(0, $this->process_thread->errno());
    }

}

class MyThread extends \Jenner\SimpleFork\Process{
    public function run(){
        for($i=0; $i<3; $i++){
            echo "thread pid:" . getmypid() . PHP_EOL;
        }
    }
}

class MyRunnable implements \Jenner\SimpleFork\Runnable{

    /**
     * process entry
     * @return mixed
     */
    public function run()
    {
        for($i=0; $i<3; $i++){
            echo "runnable pid:" . getmypid() . PHP_EOL;
        }
    }
}