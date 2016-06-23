<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/25 10:55
 */

spl_autoload_register(function ($classname) {
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    if (strstr($classname, "\\Jenner\\SimpleFork\\") === 0) {
        $file = $dir . basename(str_replace('\\', '/', $classname)) . '.php';
        if (file_exists($file)) require $file;
    }
});