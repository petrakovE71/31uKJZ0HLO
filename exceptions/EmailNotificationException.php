<?php

namespace app\exceptions;

/**
 * EmailNotificationException - thrown when email notification fails
 *
 * Used for email sending errors, invalid email addresses, mailer configuration issues
 */
class EmailNotificationException extends StoryVaultException
{
    public function __construct(
        $message = "Email notification failed",
        $userMessage = "Не удалось отправить email уведомление",
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}
