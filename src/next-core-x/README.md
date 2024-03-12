中文 | [English](./README-en.md)

# NextCoreX

基于 hyperf 实现的基础 channel 包、提供最基本的 `pull` `push` `subscribe` `publish` 功能
内置实现了 `orm` `redi` `rabbitmq` 驱动

## 安装

```shell
composer require mine/next-core-x
```

# 使用

## 通过定时器进行 `pull` `push` 处理

```php
// app/Process/ImProcess.php
use Mine\NextCoreX\Queue;
use Hyperf\WebSocketServer\Sender;
class ImProcess extends AbstractProcess
{
    public function __construct(
      private readonly Queue $queue,
      private readonly Sender $sender
    ) {}
    public function handle()
    {
        $data = Queue::pull('user_messages');
        foreach ($data as $item) {
            
        }
    }
}
```