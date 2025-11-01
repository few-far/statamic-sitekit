<?php

namespace FewFar\Sitekit\Exceptions;

use RuntimeException;
use Throwable;

class AppException extends RuntimeException
{
    /**
     * Creates an instance of the exception.
     *
     * @param array $context given to the exception handler.
     */
    public function __construct(
        string $message,
        protected array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Called by the exception handler when being reported.
     */
    public function context() : array
    {
        return $this->context;
    }

    /**
     * Throws exception locally, throws exception in other environments.
     *
     * @throws static
     */
    public static function unexpected(
        string $message,
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    )
    {
        $exception = new static(...func_get_args());

        if (app()->isLocal()) {
            throw $exception;
        }

        report($exception);
    }
}
