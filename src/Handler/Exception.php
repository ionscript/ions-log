<?php

namespace Ions\Log\Handler;

use Ions\Log\Log;

/**
 * Class Exception
 * @package Ions\Log\Handler
 */
final class Exception
{
    /**
     * @param Log $log
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public static function register(Log $log)
    {
        $errorPriorityMap = $log::$errorPriorityMap;

        set_exception_handler(function ($exception) use ($log, $errorPriorityMap) {

            $logMessages = [];

            do {
                $priority = $log::ERR;

                if ($exception instanceof \ErrorException && isset($errorPriorityMap[$exception->getSeverity()])) {
                    $priority = $errorPriorityMap[$exception->getSeverity()];
                }

                $extra = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace()
                ];

                if (isset($exception->xdebug_message)) {
                    $extra['xdebug'] = $exception->xdebug_message;
                }

                $logMessages[] = [
                    'priority' => $priority,
                    'message' => $exception->getMessage(),
                    'extra' => $extra
                ];

                $exception = $exception->getPrevious();

            } while ($exception);

            foreach (array_reverse($logMessages) as $logMessage) {
                $log->log(
                    $logMessage['priority'],
                    $logMessage['message'],
                    $logMessage['extra']
                );
            }
        });
    }

    /**
     * @return void
     */
    public static function unregister()
    {
        restore_exception_handler();
    }
}
