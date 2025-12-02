<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: FileSizeRotateHandler.php
 * @Date: 2025-12-2
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

class FileSizeRotateHandler extends StreamHandler
{
    private int $maxSize;

    private int $keepDays;

    public function __construct(
        string $filename,
        int $maxSize,
        int $keepDays = 30,
        int|string|Level $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->maxSize  = $maxSize;
        $this->keepDays = $keepDays;
        
        parent::__construct($filename, $level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        // 在写入前检查是否需要轮转
        $this->checkRotation();
        
        parent::write($record);
    }

    /**
     * 检测文件是否超过大小，若超过则切分.
     */
    private function checkRotation(): void
    {
        // 使用 $this->url (StreamHandler 的属性)
        if (! file_exists($this->url)) {
            return;
        }

        // 清除文件状态缓存，确保获取准确大小
        clearstatcache(true, $this->url);
        $fileSize = filesize($this->url);

        if ($fileSize === false || $fileSize < $this->maxSize) {
            return;
        }

        $pathInfo = pathinfo($this->url);
        $dir      = $pathInfo['dirname'];
        $base     = $pathInfo['filename'];
        $ext      = $pathInfo['extension'] ?? 'log';

        $date  = date('Y-m-d');
        $index = 1;

        // 找到下一个未存在的编号
        // 格式: filename-2025-11-24-1.log
        do {
            $newName = sprintf('%s/%s-%s-%d.%s', $dir, $base, $date, $index, $ext);
            ++$index;
        } while (file_exists($newName));

        // 尝试关闭当前流
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null; // 重置流对象

        // 重命名旧文件 (原子操作)
        if (rename($this->url, $newName)) {
            // 只有重命名成功才清理旧日志
            $this->cleanupOldLogs($dir, $base, $ext);
        }

        // 下一次 write 会自动通过 parent::write -> stream_open 重新打开原文件名的新文件
    }

    /**
     * 清理超过 keepDays 的日志文件.
     */
    private function cleanupOldLogs(string $dir, string $base, string $ext): void
    {
        // 查找匹配模式的文件: filename-*.log
        $files = glob(sprintf('%s/%s-*.%s', $dir, $base, $ext));
        if (! $files) {
            return;
        }

        $now           = time();
        $expireSeconds = $this->keepDays * 86400;

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $mtime = filemtime($file);
            // 确保获取到了修改时间
            if ($mtime === false) {
                continue;
            }

            if (($now - $mtime) > $expireSeconds) {
                @unlink($file);
            }
        }
    }
}