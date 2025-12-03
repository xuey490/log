
这是一个基于monolog开发的，可以自动分割日志的类库。日志自动按大小分片保存，超过大小后自动分割，并保留指定天数的日志

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

### 日志片段  /storage/logs/app.log

```
[2025-12-03T07:29:22.869860+08:00] app.INFO: [Event Expired] Listener files changed or cache expired. Rescanning... [] []
[2025-12-03T07:29:22.926525+08:00] app.INFO: [Event] Scanned and found 2 subscribers. [] []
[2025-12-03T07:29:24.227385+08:00] app.INFO: [Route Loaded]Loaded 128 routes (manual: 5, annotated: 123) [] []
[2025-12-03T07:29:30.594206+08:00] app.INFO: [Event Scan] Subscribers loaded from cache (fingerprint match). [] []
[2025-12-03T07:29:30.606746+08:00] app.INFO: [Route Loaded]Loaded 128 routes (manual: 5, annotated: 123) [] []
[2025-12-03T07:29:40.230684+08:00] app.INFO: [Event Scan] Subscribers loaded from cache (fingerprint match). [] []
[2025-12-03T07:29:40.242940+08:00] app.INFO: [Route Loaded]Loaded 128 routes (manual: 5, annotated: 123) [] []
[2025-12-03T07:29:42.975919+08:00] app.INFO: [Request processed] {"method":"GET","path":"/blog","status":200,"duration":"2728.64ms","ip":"::1"} []
```

SQL debug log
```
[2025-12-03T07:44:02.805026+08:00] app.DEBUG: [ThinkORM Info] {"sql":"CONNECT:[ UseTime:0.018970s ] mysql:host=127.0.0.1;port=3306;dbname=oa;charset=utf8mb4","time":"1,764,719,042.804996s","explain":[]} []
[2025-12-03T07:44:02.814690+08:00] app.DEBUG: [ThinkORM Info] {"sql":"SHOW FULL COLUMNS FROM `oa_custom`","time":"0.009247s","explain":[]} []
[2025-12-03T07:44:02.833638+08:00] app.DEBUG: [ThinkORM Info] {"sql":"SELECT * FROM `oa_custom` WHERE  `status` = 1 ORDER BY id desc","time":"0.000428s","explain":[]} []
[2025-12-03T07:44:02.944503+08:00] app.DEBUG: [ThinkORM Info] {"sql":"SELECT COUNT(*) AS think_count FROM `oa_custom` WHERE  `status` = 1","time":"0.022348s","explain":[]} []
[2025-12-03T07:44:02.945115+08:00] app.DEBUG: [ThinkORM Info] {"sql":"SELECT * FROM `oa_custom` WHERE  `status` = 1 LIMIT 0,3","time":"0.000349s","explain":[]} []
```

