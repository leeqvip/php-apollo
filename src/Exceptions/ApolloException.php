<?php

declare(strict_types=1);

namespace Leeqvip\Apollo\Exceptions;

/**
 * Apollo client exception
 */
class ApolloException extends \Exception
{
    /**
     * Response data
     * @var mixed
     */
    protected mixed $context;

    /**
     * Constructor
     * 
     * @param string $message Exception message
     * @param int $code Exception code
     * @param mixed $response Response data
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, $context = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get response data
     * 
     * @return mixed
     */
    public function getContext(): mixed
    {
        return $this->context;
    }
}
