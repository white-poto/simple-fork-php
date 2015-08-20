<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:28
 */

namespace Jenner\SimpleFork;


abstract class Runnable extends \Exception
{
    /**
     * process entry
     * @return mixed
     */
    abstract public function run();
}