<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/11 17:50
 */

namespace Jenner\SimpleFork;


class Utils
{
    public static function checkOverwriteRunMethod($child_class)
    {
        $parent_class = '\\Jenner\\SimpleFork\\Process';
        if ($child_class == $parent_class) {
            $message = "you should extend the `{$parent_class}`" .
                " and overwrite the run method";
            throw new \RuntimeException($message);
        }

        $child = new \ReflectionClass($child_class);
        $parent_methods = $child->getParentClass()->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($parent_methods as $parent_method) {
            if ($parent_method->getName() !== 'run') continue;

            $declaring_class = $child->getMethod($parent_method->getName())
                ->getDeclaringClass()
                ->getName();

            echo $declaring_class . PHP_EOL;
            echo $parent_class . PHP_EOL;
            if ($declaring_class === $parent_class) {
                $message = "you must overwrite the run method";
                throw new \RuntimeException($message);
            }
        }
    }
}