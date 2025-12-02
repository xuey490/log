
这是一个可以基于monolog开发的，可以自动分割日志的类库。
A custom logging library for FssPHP Framework based on Monolog with file size rotation support.

### 安装

```
composer require xuey490/log
```

### 初始化使用

```
use Framework\Log\LoggerService;

$config = [
    'log_channel'   => 'my-app',
    'log_path'      => __DIR__ . '/storage/logs',
    'log_size'      => 10 * 1024 * 1024, // 10MB
    'log_keep_days' => 15
];

$logger = new LoggerService($config);
$logger->info("Log system initialized successfully!");

```