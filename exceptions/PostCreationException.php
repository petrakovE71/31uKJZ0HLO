<?php

namespace app\exceptions;

/**
 * PostCreationException - thrown when post creation fails
 *
 * Used for database errors, validation failures during post creation
 */
class PostCreationException extends StoryVaultException
{
    public function __construct(
        $message = "Post creation failed",
        $userMessage = "Не удалось создать пост. Попробуйте еще раз.",
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}
