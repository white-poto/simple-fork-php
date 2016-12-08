<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/24 18:38
 */

namespace Jenner\SimpleFork\Queue;


class PipeQueue implements QueueInterface
{
    /**
     * @var Pipe
     */
    protected $pipe;

    /**
     * @var bool
     */
    protected $block;

    /**
     * @param string $filename fifo filename
     * @param int $mode
     * @param bool $block if blocking
     */
    public function __construct($filename = '/tmp/simple-fork.pipe', $mode = 0666)
    {
        $this->pipe = new Pipe($filename, $mode);
        $this->block = false;
        $this->pipe->setBlock($this->block);
    }

    /**
     * put value into the queue of channel
     *
     * @param $value
     * @return bool
     */
    public function put($value)
    {
        $len = strlen($value);
        if ($len > 2147483647) {
            throw new \RuntimeException('value is too long');
        }
        $raw = pack('N', $len) . $value;
        $write_len = $this->pipe->write($raw);

        return $write_len == strlen($raw);
    }

    /**
     * get value from the queue of channel
     *
     * @param bool $block if block when the queue is empty
     * @return bool|string
     */
    public function get($block = false)
    {
        if ($this->block != $block) {
            $this->pipe->setBlock($block);
            $this->block = $block;
        }
        $len = $this->pipe->read(4);
        if ($len === false) {
            throw new \RuntimeException('read pipe failed');
        }

        if (strlen($len) === 0) {
            return null;
        }
        $len = unpack('N', $len);
        if (empty($len) || !array_key_exists(1, $len) || empty($len[1])) {
            throw new \RuntimeException('data protocol error');
        }
        $len = intval($len[1]);

        $value = '';
        while (true) {
            $temp = $this->pipe->read($len);
            if (strlen($temp) == $len) {
                return $temp;
            }
            $value .= $temp;
            $len -= strlen($temp);
            if ($len == 0) {
                return $value;
            }
        }
    }

    /**
     * remove the queue resource
     *
     * @return bool
     */
    public function remove()
    {
        $this->pipe->close();
        $this->pipe->remove();
    }
}