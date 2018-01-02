<?php

namespace Ions\Log\Logger;

/**
 * Class Console
 * @package Ions\Log\Logger
 */
class Console
{
    const COLOR_GREEN = "\033[32m";
    const COLOR_RED = "\033[31m";
    const COLOR_RESET = "\033[0m";

    const HIGHLIGHT_INFO = 'info';
    const HIGHLIGHT_ERROR = 'error';

    /**
     * @var array
     */
    private $highlightMap = [
        self::HIGHLIGHT_INFO => self::COLOR_GREEN,
        self::HIGHLIGHT_ERROR => self::COLOR_RED
    ];

    /**
     * @var string
     */
    private $eol = PHP_EOL;

    /**
     * @var bool|resource
     */
    private $stderr = STDERR;

    /**
     * @var bool
     */
    private $supportsColor;

    /**
     * Console constructor.
     * @param bool|resource $resource
     */
    public function __construct($resource = STDOUT)
    {
        $this->supportsColor = $this->detectColorCapabilities($resource);
    }

    /**
     * @param $string
     * @return mixed
     */
    public function colorize($string)
    {
        $reset = $this->supportsColor ? self::COLOR_RESET : '';

        foreach ($this->highlightMap as $key => $color) {
            $pattern = sprintf('#<%s>(.*?)</%s>#s', $key, $key);
            $color = $this->supportsColor ? $color : '';
            $string = preg_replace($pattern, $color . '$1' . $reset, $string);
        }

        return $string;
    }

    /**
     * @param $string
     * @param bool $colorize
     * @param bool|resource $resource
     */
    public function write($string, $colorize = true, $resource = STDOUT)
    {
        if ($colorize) {
            $string = $this->colorize($string);
        }

        $string = $this->formatNewlines($string);

        fwrite($resource, $string);
    }

    /**
     * @param $string
     * @param bool $colorize
     * @param bool|resource $resource
     */
    public function writeLine($string, $colorize = true, $resource = STDOUT)
    {
        $this->write($string . $this->eol, $colorize, $resource);
    }

    /**
     * @param $message
     */
    public function writeErrorMessage($message)
    {
        $this->writeLine(sprintf('<error>%s</error>', $message), true, $this->stderr);

        $this->writeLine('', false, $this->stderr);
    }

    /**
     * @param bool|resource $resource
     * @return bool
     */
    private function detectColorCapabilities($resource = STDOUT)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
        }

        return function_exists('posix_isatty') && posix_isatty($resource);
    }

    /**
     * @param $string
     * @return mixed
     */
    private function formatNewlines($string)
    {
        $string = str_replace($this->eol, "\0PHP_EOL\0", $string);
        $string = preg_replace("/(\r\n|\n|\r)/", $this->eol, $string);

        return str_replace("\0PHP_EOL\0", $this->eol, $string);
    }
}
