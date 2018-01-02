<?php

namespace Ions\Log\Handler;

use Ions\Log\Log;

/**
 * Class Shutdown
 * @package Ions\Log\Handler
 */
final class Shutdown
{
    /**
     * @param Log $log
     * @return bool
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public static function register(Log $log)
    {
        $errorPriorityMap = $log::$errorPriorityMap;

        register_shutdown_function(function () use ($log, $errorPriorityMap) {

            $error = error_get_last();

            if (null === $error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING], true)) {
                return;
            }

            $extra = [
                'file' => $error['file'],
                'line' => $error['line']
            ];

            $log->log($errorPriorityMap[$error['type']], $error['message'], $extra);
        });

        return true;
    }

    /**
     * @return void
     */
    public static function unregister()
    {
    }
}
