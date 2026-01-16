<?php

declare(strict_types=1);

namespace ProgressiveBar\Exceptions;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = 'Method Not Allowed')
    {
        parent::__construct($message, 405);
    }
}
