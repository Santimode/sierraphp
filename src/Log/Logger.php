<?php
declare(strict_types=1);
namespace Sierra\Log;

final class Logger implements LoggerInterface
{
    public function __construct(private ?string $filePath = null) {}

    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $formattedMessage = $this->interpolate($message, $context);
        $timestamp = date('Y-m-d H:i:s');
        $upperLevel = strtoupper($level);

        $contextString = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $line = sprintf("[%s] [%s] %s%s\n", $timestamp, $upperLevel, $formattedMessage, $contextString);

        if ($this->filePath !== null) {
            $dir = dirname($this->filePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
        } else {
            error_log(trim($line));
        }
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    private function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string)$val;
            }
        }
        return strtr($message, $replace);
    }
}
