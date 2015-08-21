<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:11
 */

namespace Jenner\SimpleFork\Queue;

interface QueueInterface
{
    /**
     * put value into the queue of channel
     * @param $channel
     * @param $value
     * @return mixed
     */
    public function put($channel, $value);

    /**
     * get value from the queue of channel
     * @param $channel
     * @return mixed
     */
    public function get($channel);

    /**
     * get the size of the queue of channel
     * @param $channel
     * @return mixed
     */
    public function size($channel);

    /**
     * remove the queue resource
     * @return mixed
     */
    public function remove();
}