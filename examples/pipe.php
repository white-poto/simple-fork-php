<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/25 11:07
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


$pid = pcntl_fork();

if($pid == 0){
    $pipe = new \Jenner\SimpleFork\Queue\Pipe();
    sleep(1);
    $pipe->write("test");
    sleep(1);
}else{
    $pipe = new \Jenner\SimpleFork\Queue\Pipe();
    $pipe->setBlock(true);
    $result = $pipe->read(4);
    echo $result . PHP_EOL;
}