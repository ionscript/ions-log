<?php

namespace Ions\Log;

use DateTime;
use Ions\Std\Spl\SplPriorityQueue;

/**
 * Logging messages with a stack of backends
 */
class Log implements LogInterface
{
    const EMERG = 0;
    const ALERT = 1;
    const CRIT = 2;
    const ERR = 3;
    const WARN = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    const PRIORITIES = [
        self::EMERG => 'EMERG',
        self::ALERT => 'ALERT',
        self::CRIT => 'CRIT',
        self::ERR => 'ERR',
        self::WARN => 'WARN',
        self::NOTICE => 'NOTICE',
        self::INFO => 'INFO',
        self::DEBUG => 'DEBUG'
    ];
    
    public static $errorPriorityMap = [
        E_NOTICE => self::NOTICE,
        E_USER_NOTICE => self::NOTICE,
        E_WARNING => self::WARN,
        E_CORE_WARNING => self::WARN,
        E_USER_WARNING => self::WARN,
        E_ERROR => self::ERR,
        E_USER_ERROR => self::ERR,
        E_CORE_ERROR => self::ERR,
        E_RECOVERABLE_ERROR => self::ERR,
        E_PARSE => self::ERR,
        E_COMPILE_ERROR => self::ERR,
        E_COMPILE_WARNING => self::ERR,
        E_STRICT => self::DEBUG,
        E_DEPRECATED => self::DEBUG,
        E_USER_DEPRECATED => self::DEBUG
    ];

    /**
     * @var SplPriorityQueue
     */
    private $loggers;

    /**
     * @var array
     */
    private $options;

    /**
     * Log constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->loggers = new SplPriorityQueue;
        $this->options = $options;
    }

    /**
     * @param $logger
     * @param int $priority
     * @return $this
     */
    public function set($logger, $priority = 1)
    {
        $this->loggers->insert($logger, $priority);

        return $this;
    }

    /**
     * @return $this
     */
    public function setOutputLogger()
    {
        $this->set(new Logger\Output);

        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFileLogger($filename = '')
    {
        $file = $this->options['path'] . '/' . ($filename ?: $this->options['filename']);

        $this->set(new Logger\File($file));

        return $this;
    }

    /**
     * @param $priority
     * @param $message
     * @param array $extra
     * @return $this
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function log($priority, $message, $extra = [])
    {
        if (!is_int($priority) || ($priority < 0) || ($priority >= count(static::PRIORITIES))) {
            throw new \InvalidArgumentException(sprintf(
                '$priority must be an integer >= 0 and < %d; received %s',
                count(static::PRIORITIES),
                var_export($priority, 1)
            ));
        }

        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new \InvalidArgumentException(
                '$message must implement magic __toString() method'
            );
        }

        if (!is_array($extra)) {
            throw new \InvalidArgumentException(
                '$extra must be an array'
            );
        }

        if ($this->loggers->count() === 0) {
            throw new \RuntimeException('No logger specified');
        }

        $timestamp = new DateTime();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        $output = [
            'timestamp' => $timestamp,
            'priority' => (int)$priority,
            'priorityName' => static::PRIORITIES[$priority],
            'message' => (string)$message,
            'extra' => $extra,
        ];

        foreach ($this->loggers->toArray() as $log) {
            $log->write($output);
        }

        return $this;
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function emerg($message, array $extra = [])
    {
        return $this->log(self::EMERG, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function alert($message, array $extra = [])
    {
        return $this->log(self::ALERT, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function crit($message, array $extra = [])
    {
        return $this->log(self::CRIT, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function err($message, array $extra = [])
    {
        return $this->log(self::ERR, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function warn($message, array $extra = [])
    {
        return $this->log(self::WARN, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function notice($message, array $extra = [])
    {
        return $this->log(self::NOTICE, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function info($message, array $extra = [])
    {
        return $this->log(self::INFO, $message, $extra);
    }

    /**
     * @param $message
     * @param array $extra
     * @return Log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function debug($message, array $extra = [])
    {
        return $this->log(self::DEBUG, $message, $extra);
    }

    /**
     * @param $var
     * @param string $label
     * @param bool $echo
     * @return mixed|string
     */
    public static function dump($var, $label = 'DUMP', $echo = true)
    {
        $label = !$label ? '' : rtrim($label) . ' ';

        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);

        if (PHP_SAPI === 'cli') {
            $output = PHP_EOL . $label . PHP_EOL . $output . PHP_EOL;
        } else {
            $output = '<pre>' . $label . $output . '</pre>';
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}
