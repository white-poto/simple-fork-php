<?php

/**
 * @author Jenner <hypxm@qq.com>
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/11 17:59
 */
class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testCheck()
    {
        $process = new UtilsTestProcess();
        \Jenner\SimpleFork\Utils::checkOverwriteRunMethod(get_class($process));
    }

    public function testError(){
        $this->setExpectedException("RuntimeException");
        \Jenner\SimpleFork\Utils::checkOverwriteRunMethod(get_class(new \Jenner\SimpleFork\Process()));
    }
}

class UtilsTestProcess extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        echo 'run' . PHP_EOL;
    }
}