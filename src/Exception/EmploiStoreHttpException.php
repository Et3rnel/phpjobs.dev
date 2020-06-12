<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class EmploiStoreHttpException extends Exception
{
    public function __construct(int $statusCode = null, $code = 0, Throwable $previous = null)
    {
        $message = '';
        if ($statusCode) {
            $message = "Error with status code {$statusCode}";
        }

        parent::__construct($message, $code, $previous);
    }
}