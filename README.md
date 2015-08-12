SimpleFork
===================
基于PCNTL扩展的进程管理包，接口类似与Java的Thread和Runnable 

特性
---------------------------
+ 提供进程池
+ 自动处理僵尸进程回收，支持无阻塞调用
+ 提供共享内存、System V 消息队列、Semaphore锁，方便IPC通信（进程通信）
+ 提供Process和Runnable两种方式实现进程
+ 可以实时获取到进程状态

注意是选项
-----------------------
+ System V 消息队列由于在程序退出时可能存在尚未处理完的数据，所以不会销毁。如果需要销毁，请调用$queue->remove()方法删除队列
+ 共享内存会在所有进程退出后删除
+ Semaphore对象会在对象回收时进行销毁
+ 进程池start()后，需要调用wait()进行僵尸进程回收，可以无阻塞调用

示例程序
-------------------------
更多示例程序见exmples目录  
simple.php  
```php
class TestRunnable extends \Jenner\SimpleFork\Runnable{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        echo "I am a sub process" . PHP_EOL;
    }
}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();
```

shared_memory.php
```php
class Producer extends \Jenner\SimpleFork\Process{
    public function run(){
        for($i = 0; $i<10; $i++){
            $this->cache->set($i, $i);
            echo "set {$i} : {$i}" . PHH_EOL;
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process{
    public function run(){
        sleep(5);
        for($i=0; $i<10; $i++){
            echo "get {$i} : " . $this->cache->get($i) . PHP_EOL;
        }
    }
}

$memory = new \Jenner\SimpleFork\IPC\SharedMemory();
$producer = new Producer();
$producer->setCache($memory);

$worker = new Worker();
$worker->setCache($memory);

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();
```



