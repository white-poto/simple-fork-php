<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 17:40
 */


$tmp_file = '/tmp/' . basename(__FILE__);
touch($tmp_file);
$key = ftok($tmp_file, 'a');
$shm = shm_attach($key, 10000); //allocate shared memory
shm_put_var($shm, 1, 'test');
var_dump(shm_get_var($shm, 1));
sleep(10);
echo 'remove' . PHP_EOL;
var_dump(shm_remove($shm));
echo 'remove done' . PHP_EOL;
// here. actually the shm is still exists
sleep(10);
echo 'unset' . PHP_EOL;
unset($shm);

sleep(10);

var_dump(shm_get_var($shm, 1));


