<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/4
 * Time: 9:14
 */

declare(ticks = 1);
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$fixed_pool = new \Jenner\SimpleFork\FixedPool(new TestRunnable(), 10);
$fixed_pool->start();

$fixed_pool->keep(true);

class TestRunnable implements \Jenner\SimpleFork\Runnable {

    /**
     * process entry
     *
     * @return mixed
     */
    public function run()
    {
        echo 'sub process:' . getmypid() . PHP_EOL;
    }
}