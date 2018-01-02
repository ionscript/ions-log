<?php

namespace Ions\Log\Logger;

/**
 * Class Output
 * @package Ions\Log\Logger
 */
class Output extends Stream
{
    /**
     * @var string
     */
    protected $datetime = 'c';

    /**
     * Output constructor.
     */
    public function __construct()
    {
        parent::__construct('php://output');
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function write($data)
    {
        try {
            parent::write($this->format($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data)
    {
        if (isset($data['timestamp']) && $data['timestamp'] instanceof \DateTime) {
            $data['timestamp'] = $data['timestamp']->format($this->datetime);
        }

        if($data['extra']){
            $output = $data['priorityName'] . ' (' . $data['priority'] . ') ' . $data['message'] . ' in "' . $data['extra']['file'] . '" on line ' . $data['extra']['line'];
        } else {
            $output = $data['timestamp'] . ' ' . $data['priorityName'] . ' (' . $data['priority'] . ') ' . $data['message'];
        }

        return $output;
    }
}
