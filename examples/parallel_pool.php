<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/4
 * Time: 9:14
 */

declare(ticks = 1);
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';



class TestRunnable2 implements \Jenner\SimpleFork\Runnable {

    /**
     * process entry
     *
     * @return void
     */
    public function run()
    {
        echo 'sub process:' . getmypid() . PHP_EOL;
    }
}

$fixed_pool = new \Jenner\SimpleFork\ParallelPool(new TestRunnable2(), 10);
$fixed_pool->start();

$fixed_pool->keep(true);