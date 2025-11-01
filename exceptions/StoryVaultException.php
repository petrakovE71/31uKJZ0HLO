<?php

namespace app\exceptions;

use Exception;

/**
 * Base exception for StoryVault application
 *
 * All custom exceptions should extend this class
 */
class StoryVaultException extends Exception
{
    /**
     * @var string User-friendly error message (safe to display)
     */
    protected $userMessage;

    /**
     * Constructor
     *
     * @param string $message Technical error message (for logs)
     * @param string|null $userMessage User-friendly message (for UI)
     * @param int $code Error code
     * @param Exception|null $previous Previous exception
     */
    public function __construct(
        $message = "",
        $userMessage = null,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage ?? 'Произошла ошибка. Попробуйте позже.';
    }

    /**
     * Get user-friendly error message
     *
     * @return string
     */
    public function getUserMessage()
    {
        return $this->userMessage;
    }
}
