SimpleFork
===================
[英文README.MD](https://github.com/huyanping/simple-fork-php/blob/master/README.MD)  
基于PCNTL扩展的多进程控制框架，接口类似与Java的Thread和Runnable 

最新版本见英文版

为什么要写SimpleFork
------------------------
多进程程序的编写相比较多线程编写更加复杂，需要考虑进程回收、同步、互斥、通信等问题。对于初学者来说，处理上述问题会比较困难。
尤其是信号处理和进程通信这块，很难做到不出问题。
SimpleFork提供一套类似于JAVA多线程的进程控制接口，提供回收、同步、互斥、通信等方案，开发者可以关注业务问题，不需要过多考虑进程控制。

引入
---------------------
```bash
composer require jenner/simple_fork
```
Or
```php
require '/path/to/simple-fork-php/autoload.php'
```

依赖
----------------------
必须  
+ ext-pcntl 进程控制  

可选  
+ ext-sysvmsg 消息队列
+ ext-sysvsem 同步互斥锁
+ ext-sysvshm 共享内存
+ ext-redis redis缓存及redis队列

特性
---------------------------
+ 提供进程池
+ 自动处理僵尸进程回收，支持无阻塞调用
+ 提供共享内存、System V 消息队列、Semaphore锁，方便IPC通信（进程通信）；提供RedisCache、RedisQueue，可以用作分布式进程通信
+ 提供Process和Runnable两种方式实现进程
+ 可以实时获取到进程状态
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
+ declare(ticks=1); 这段声明用于进程信号处理。如果注册了信号处理器，程序会没执行一行代码后自动检查是否有尚未处理的信号。[http://php.net/manual/zh/control-structures.declare.php](http://php.net/manual/zh/control-structures.declare.php 官方文档)

示例程序
-------------------------
更多示例程序见[examples](https://github.com/huyanping/simple-fork-php/tree/master/examples examples)目录  
simple.php  
```php
class TestRunnable implements \Jenner\SimpleFork\Runnable{

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

callback.php  
```php
$process = new \Jenner\SimpleFork\Process(function(){
    for($i=0; $i<3; $i++){
        echo $i . PHP_EOL;
        sleep(1);
    }
});

$process->start();
$process->wait();
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

$memory = new \Jenner\SimpleFork\Cache\SharedMemory();
$producer = new Producer();
$producer->setCache($memory);

$worker = new Worker();
$worker->setCache($memory);

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute($producer);
$pool->execute($worker);
$pool->start();
$pool->wait();
```





