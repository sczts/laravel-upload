<?php


namespace Sczts\Upload\Exceptions;
use Http\Client\Exception;
use Throwable;

class UploadException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
