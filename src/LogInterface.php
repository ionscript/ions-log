<?php

namespace Ions\Log;

/**
 * Interface LogInterface
 * @package Ions\Log
 */
interface LogInterface
{
    /**
     * @param $priority
     * @param $message
     * @param array $extra
     * @return mixed
     */
    public function log($priority, $message, $extra = []);
}
