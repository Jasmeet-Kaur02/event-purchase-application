<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected $message;
    protected $statusCode;
    protected $data;


    public function __construct($message, $data, $statusCode)
    {
        $this->message = $message;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getData()
    {
        return $this->data;
    }
}
