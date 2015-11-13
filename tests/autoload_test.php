<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/13
 * Time: 11:28
 */

error_reporting(E_ALL);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Autoloader.php';


\Jenner\SimpleFork\Autoloader::register();

$process = new \Jenner\SimpleFork\Process();
