<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/6
 * Time: 14:48
 */
class AutoloaderTest extends PHPUnit_Framework_TestCase
{
    public function testAutoload(){
        \Jenner\SimpleFork\Autoloader::register();
        $this->assertTrue(class_exists("\\Jenner\\SimpleFork\\Lock\\FileLock"));
        $this->assertTrue(class_exists("\\Jenner\\SimpleFork\\Process"));
    }
}