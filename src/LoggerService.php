<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LoggerService.php
 * @Date: 2025-12-2
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Log;

use Monolog\Handler\FilterHandler;
use Monolog\Logger as MonoLogger;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LoggerService implements LoggerInterface
{
    private MonoLogger $logger;

    /**
     * @param array{
     *     log_channel?: string,
     *     log_path?: string,
     *     log_size?: int,
     *     log_keep_days?: int
     * } $config
     */
    public function __construct(array $config = [])
    {
        // 获取配置，提供默认值
        $channel  = $config['log_channel'] ?? 'app';
		
        // 默认路径改为 sys_get_temp_dir() 以防止报错，生产环境请务必配置 log_path
        $logDir   = $config['log_path'] ?? sys_get_temp_dir() . '/logs'; 
		
        $maxSize  = (int) ($config['log_size'] ?? 5 * 1024 * 1024); // 默认 5MB
		
        $keepDays = (int) ($config['log_keep_days'] ?? 30);        // 默认保留30天
		
		$dateFormat = $config['log_date_format'] ?? 'Y-m-d H:i:s'; 

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

		// 1. 定义自定义日志格式（重点：%datetime% 对应时间戳，可自定义格式）
		// 格式说明：
		// %datetime%：时间戳（后续指定格式）
		// %channel%：日志通道名
		// %level_name%：日志级别（INFO/WARNING/ERROR）
		// %message%：日志消息
		// %context%：上下文数据（数组会自动转为字符串）
		$logFormat = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

		// 2. 创建格式化器，指定时间戳格式（第二个参数是时间格式，支持 PHP 的 date() 函数格式）
		// 示例时间格式：'Y-m-d H:i:s'（不含微秒和时区）、'Y-m-d H:i:s.u'（含微秒）、'Y-m-d H:i:s P'（含时区）
		$formatter = new LineFormatter(
			$logFormat,
			$dateFormat, // 自定义时间戳格式（这里去掉了 T 分隔符和微秒，更简洁）
			true, // 是否格式化上下文数据（数组转字符串）
			true  // 是否添加换行符（默认 true，可不写）
		);

        $this->logger = new MonoLogger($channel);

        // 1. Debug 日志：仅 DEBUG
        $debugHandler = new FilterHandler(
            new FileSizeRotateHandler($logDir . '/debug.log', $maxSize, $keepDays, MonoLogger::DEBUG),
            MonoLogger::DEBUG,
            MonoLogger::DEBUG
        );
		$debugHandler->setFormatter($formatter); // 给处理器设置自定义格式化器
        $this->logger->pushHandler($debugHandler);

        // 2. Error 日志：ERROR ~ EMERGENCY
        $errorHandler = new FilterHandler(
            new FileSizeRotateHandler($logDir . '/error.log', $maxSize, $keepDays, MonoLogger::ERROR),
            MonoLogger::ERROR,
            MonoLogger::EMERGENCY
        );
		$errorHandler->setFormatter($formatter); // 给处理器设置自定义格式化器
        $this->logger->pushHandler($errorHandler);

        // 3. App 日志：INFO ~ WARNING
        $appHandler = new FilterHandler(
            new FileSizeRotateHandler($logDir . '/app.log', $maxSize, $keepDays, MonoLogger::INFO),
            MonoLogger::INFO,
            MonoLogger::WARNING
        );
		$appHandler->setFormatter($formatter); // 给处理器设置自定义格式化器
        $this->logger->pushHandler($appHandler);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function getMonoLogger(): MonoLogger
    {
        return $this->logger;
    }

    public function logRequest(Request $request, ?Response $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method'          => $request->getMethod(),
            'uri'             => $request->getRequestUri(),
            'ip'              => $request->getClientIp() ?: 'unknown',
            'user_agent'      => $request->headers->get('User-Agent') ?? 'unknown',
            'response_status' => $response?->getStatusCode(),
            'duration_ms'     => round($duration * 1000, 2),
        ]);
    }

    public function logException(Throwable $exception, Request $request): void
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
            'method'  => $request->getMethod(),
            'uri'     => $request->getRequestUri(),
            'ip'      => $request->getClientIp() ?: 'unknown',
        ]);
    }
}