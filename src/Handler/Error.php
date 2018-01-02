<?php

namespace Ions\Log\Handler;

use Ions\Log\Log;

/**
 * Class Error
 * @package Ions\Log\Handler
 */
final class Error
{
    /**
     * @param Log $log
     * @return mixed
     */
    public static function register(Log $log)
    {
        $errorPriorityMap = $log::$errorPriorityMap;

        $previous = set_error_handler(function ($level, $message, $file, $line) use ($log, $errorPriorityMap) {

            $iniLevel = error_reporting();

            if ($iniLevel & $level) {

                if (isset($errorPriorityMap[$level])) {
                    $priority = $errorPriorityMap[$level];
                } else {
                    $priority = $log::INFO;
                }

                $extra = [
                    'errno' => $level,
                    'file' => $file,
                    'line' => $line
                ];

                $log->log($priority, $message, $extra);
            }
        });

        return $previous;
    }

    /**
     * @return void
     */
    public static function unregister()
    {
        restore_error_handler();
    }
}
