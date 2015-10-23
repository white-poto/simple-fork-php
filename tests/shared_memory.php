<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 17:38
 */

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$cache = new \Jenner\SimpleFork\Cache\SharedMemory();
$cache->set('test', 'test');

var_dump($cache->remove());
var_dump($cache->get('test'));



