<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:54
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * @var \Jenner\SimpleFork\Lock\LockInterface
     */
    protected $sem;

    public function __construct()
    {
        $this->sem = \Jenner\SimpleFork\Lock\Semaphore::create("test");
    }

    /**
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->sem->acquire();
            echo "my turn: {$i} " . getmypid() . PHP_EOL;
            sleep(1);
            $this->sem->release();
        }
    }
}

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->wait();