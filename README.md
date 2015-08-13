SimpleFork
===================
基于PCNTL扩展的进程管理包，接口类似与Java的Thread和Runnable 

引入
---------------------
```bash
composer require jenner/simple_fork
```
```php
require path/to/SimpleFork/autoload.php
```

特性
---------------------------
+ 提供进程池
+ 自动处理僵尸进程回收，支持无阻塞调用
+ 提供共享内存、System V 消息队列、Semaphore锁，方便IPC通信（进程通信）
+ 提供Process和Runnable两种方式实现进程
+ 可以实时获取到进程状态
+ shutdown所有进程或单独stop一个进程时，可以注册覆盖beforeExit()方法，返回true则退出，false继续运行（在某些场景，进程不能立即退出）
+ 支持子进程运行时reload

注意事项
-----------------------
+ System V 消息队列由于在程序退出时可能存在尚未处理完的数据，所以不会销毁。如果需要销毁，请调用$queue->remove()方法删除队列
+ 共享内存会在所有进程退出后删除
+ Semaphore对象会在对象回收时进行销毁
+ 进程池start()后，需要调用wait()进行僵尸进程回收，可以无阻塞调用
+ 获取进程状态(调用isAlive()方法)前，最好调用一个无阻塞的wait(false)进行一次回收，由于进程运行状态的判断不是原子操作，所以isAlive()方法不保证与实际状态完全一致
+ 如果你不清楚在什么情况下需要在程序的最开始加入declare(ticks=1);，那么最好默认第一行都加入这段声明。

如何使用declare(ticks=1);
--------------------------
+ declare(ticks=1); 这段声明用于进程信号处理

TODO
---------------------------
+ 提供更多功能的进程池，模仿java
+ 提供第三方进程通信机制（Redis等）
+ 更多的测试及示例程序

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



