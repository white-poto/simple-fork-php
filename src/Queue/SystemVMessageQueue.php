<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:15
 */

namespace Jenner\SimpleFork\Queue;


class SystemVMessageQueue implements QueueInterface
{
    /**
     * 消息分组类型，用于将一个消息队列中的信息进行分组
     * @var int
     */
    protected $msg_type;

    /**
     * 队列标志
     * @var
     */
    protected $queue;

    /**
     * 是否序列化
     * @var bool
     */
    protected $serialize_needed;

    /**
     * 无法写入队列时，是否阻塞
     * @var bool
     */
    protected $block_send;

    /**
     * 设置位MSG_IPC_NOWAIT，如果无法获取到一个消息，则不等待；如果设置位NULL，则会等待消息到来
     * @var int
     */
    protected $option_receive;

    /**
     * 希望接收到的最大消息大小
     * @var int
     */
    protected $maxsize;

    /**
     * IPC通信KEY
     * @var
     */
    protected $key_t;

    protected $ipc_filename;

    /**
     * @param int $channel 消息类型
     * @param string $ipc_filename IPC通信标志文件，用于获取唯一IPC KEY
     * @param bool $serialize_needed 是否序列化
     * @param bool $block_send 无法写入队列时，是否阻塞
     * @param int $option_receive 设置位MSG_IPC_NOWAIT，如果无法获取到一个消息，则不等待；如果设置位NULL，则会等待消息到来
     * @param int $maxsize 希望接收到的最大消息
     */
    public function __construct(
        $channel = 1,
        $ipc_filename = __FILE__,
        $serialize_needed = true,
        $block_send = true,
        $option_receive = MSG_IPC_NOWAIT,
        $maxsize = 100000
    )
    {
        $this->ipc_filename = $ipc_filename;
        $this->msg_type = $channel;
        $this->serialize_needed = $serialize_needed;
        $this->block_send = $block_send;
        $this->option_receive = $option_receive;
        $this->maxsize = $maxsize;
        $this->initQueue($ipc_filename, $channel);
    }

    /**
     * 初始化一个队列
     * @param $ipc_filename
     * @param $msg_type
     * @throws \Exception
     */
    protected function initQueue($ipc_filename, $msg_type)
    {
        $this->key_t = $this->getIpcKey($ipc_filename, $msg_type);
        $this->queue = \msg_get_queue($this->key_t);
        if (!$this->queue) throw new \RuntimeException('msg_get_queue failed');
    }

    /**
     * @param $ipc_filename
     * @param $msg_type
     * @throws \Exception
     * @return int
     */
    public function getIpcKey($ipc_filename, $msg_type)
    {
        if (!file_exists($ipc_filename)) {
            $create_file = touch($ipc_filename);
            if ($create_file === false) {
                $message = "ipc_file is not exists and create failed";
                throw new \RuntimeException($message);
            }
        }

        $key_t = \ftok($ipc_filename, $msg_type);
        if ($key_t == 0) throw new \RuntimeException('ftok error');

        return $key_t;
    }

    /**
     * 从队列获取一个
     * @param $channel
     * @return bool
     * @throws \Exception
     */
    public function get($channel)
    {
        $this->msg_type = $channel;
        $queue_status = $this->status();
        if ($queue_status['msg_qnum'] > 0) {
            if (\msg_receive(
                    $this->queue,
                    $this->msg_type,
                    $msgtype_erhalten,
                    $this->maxsize, $data,
                    $this->serialize_needed,
                    $this->option_receive,
                    $err
                ) === true
            ) {
                return $data;
            } else {
                throw new \RuntimeException($err);
            }
        } else {
            return false;
        }
    }

    /**
     * 写入队列
     * @param $channel
     * @param $message
     * @return bool
     * @throws \Exception
     */
    public function put($channel, $message)
    {
        $this->msg_type = $channel;
        if (!\msg_send($this->queue, $this->msg_type, $message, $this->serialize_needed, $this->block_send, $err) === true) {
            throw new \RuntimeException($err);
        }

        return true;
    }

    /*
     * 返回值数组下标如下：
     * msg_perm.uid	 The uid of the owner of the queue. 用户ID
     * msg_perm.gid	 The gid of the owner of the queue. 用户组ID
     * msg_perm.mode	 The file access mode of the queue. 访问模式
     * msg_stime	 The time that the last message was sent to the queue. 最后一次队列写入时间
     * msg_rtime	 The time that the last message was received from the queue.  最后一次队列接收时间
     * msg_ctime	 The time that the queue was last changed. 最后一次修改时间
     * msg_qnum	 The number of messages waiting to be read from the queue. 当前等待被读取的队列数量
     * msg_qbytes	 The maximum number of bytes allowed in one message queue.  一个消息队列中允许接收的最大消息总大小
     *               On Linux, this value may be read and modified via /proc/sys/kernel/msgmnb.
     * msg_lspid	 The pid of the process that sent the last message to the queue. 最后发送消息的进程ID
     * msg_lrpid	 The pid of the process that received the last message from the queue. 最后接收消息的进程ID
     *
     * @return array
     */
    /**
     * @return array
     */
    public function status()
    {
        $queue_status = \msg_stat_queue($this->queue);
        return $queue_status;
    }

    /**
     * 获取队列当前堆积状态
     * @param $channel
     * @return mixed
     */
    public function size($channel)
    {
        $this->msg_type = $channel;
        $status = $this->status();

        return $status['msg_qnum'];
    }

    /**
     * allows you to change the values of the msg_perm.uid,
     * msg_perm.gid, msg_perm.mode and msg_qbytes fields of the underlying message queue data structure
     * 可以用来修改队列运行接收的最大读取的数据
     *
     * @param string $key 状态下标
     * @param int $value 状态值
     * @return bool
     */
    public function setStatus($key, $value)
    {
        $this->checkSetPrivilege($key);
        if ($key == 'msg_qbytes')
            return $this->setMaxQueueSize($value);
        $queue_status[$key] = $value;

        return \msg_set_queue($this->queue, $queue_status);
    }

    /**
     * 删除一个队列
     * @return bool
     */
    public function remove()
    {
        return \msg_remove_queue($this->queue);
    }

    /**
     * 修改队列能容纳的最大字节数，需要root权限
     * @param $size
     * @throws \Exception
     * @return bool
     */
    public function setMaxQueueSize($size)
    {
        $user = \get_current_user();
        if ($user !== 'root')
            throw new \Exception('changing msg_qbytes needs root privileges');

        return $this->setStatus('msg_qbytes', $size);
    }

    /**
     * 判断一个队列是否存在
     * @param $key
     * @return bool
     */
    public function queueExists($key)
    {
        return \msg_queue_exists($key);
    }

    /**
     * 检查修改队列状态的权限
     * @param $key
     * @throws \Exception
     */
    private function checkSetPrivilege($key)
    {
        $privilege_field = array('msg_perm.uid', 'msg_perm.gid', 'msg_perm.mode');
        if (!\in_array($key, $privilege_field)) {
            $message = 'you can only change msg_perm.uid, msg_perm.gid, " .
            " msg_perm.mode and msg_qbytes. And msg_qbytes needs root privileges';

            throw new \RuntimeException($message);
        }
    }

    /**
     * init when wakeup
     */
    public function __wakeup()
    {
        $this->initQueue($this->ipc_filename, $this->msg_type);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this);
    }
}