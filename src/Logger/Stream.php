<?php

namespace Ions\Log\Logger;

/**
 * Class Stream
 * @package Ions\Log\Logger
 */
class Stream
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * Stream constructor.
     * @param $stream
     * @param string $mode
     * @param int $chmod
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function __construct($stream, $mode = 'ab', $chmod = 0777)
    {

        if (null === $mode) {
            $mode = 'ab';
        }

        if (is_resource($stream)) {
            if ('stream' !== get_resource_type($stream)) {
                throw new \InvalidArgumentException(sprintf(
                    'Resource is not a stream; received "%s',
                    get_resource_type($stream)
                ));
            }

            if ('ab' !== $mode) {
                throw new \InvalidArgumentException(sprintf('Mode must be "a" on existing streams; received "%s"', $mode));
            }

            $this->stream = $stream;

        } else {

            if ($chmod && !file_exists($stream) && is_writable(dirname($stream))) {
                touch($stream);
                chmod($stream, $chmod);
            }

            $this->stream = fopen($stream, $mode, false);

            if (!$this->stream) {
                throw new \RuntimeException(sprintf('"%s" cannot be opened with mode "%s"', $stream, $mode));
            }
        }
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param $string
     */
    protected function write($string)
    {
        $line = $string . PHP_EOL;

        fwrite($this->stream, $line);
    }

    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
