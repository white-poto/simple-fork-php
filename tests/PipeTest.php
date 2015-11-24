<?php

/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/24 17:00
 */
class PipeTest extends PHPUnit_Framework_TestCase
{
    public function testRead()
    {

        $process = new \Jenner\SimpleFork\Process(function () {
            $pipe = new \Jenner\SimpleFork\Queue\Pipe();
            $pipe->write('test');
            sleep(2);
            $pipe->close();
        });
        $process->start();
        sleep(1);
        $pipe = new \Jenner\SimpleFork\Queue\Pipe();
        $this->assertEquals('test', $pipe->read());
        $process->wait(true);
        $pipe->close();
    }

    public function testWrite()
    {
        $pipe = new \Jenner\SimpleFork\Queue\Pipe();
        $this->assertEquals(4, $pipe->write('test'));

        $process = new \Jenner\SimpleFork\Process(function () {
            $pipe = new \Jenner\SimpleFork\Queue\Pipe();
            $pipe->read();
            $pipe->close();
        });
        $process->start();
        $process->wait(true);
        $pipe->close();
    }

    public function testBlock()
    {
        $pipe = new \Jenner\SimpleFork\Queue\Pipe();
        $pipe->setBlock(true);
        $process = new \Jenner\SimpleFork\Process(function () {
            $pipe = new \Jenner\SimpleFork\Queue\Pipe();
            sleep(2);
            $pipe->write('test');
            $pipe->close();
        });
        $start = time();
        $process->start();
        $this->assertEquals('test', $pipe->read(4));
        $end = time();
        $this->assertTrue(($end - $start) >= 2);
        $process->wait(true);
    }
}