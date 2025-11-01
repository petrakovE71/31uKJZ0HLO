<?php

namespace app\exceptions;

/**
 * RateLimitException - thrown when rate limit is exceeded
 *
 * Used when user tries to post more frequently than allowed (1 post per 3 minutes)
 */
class RateLimitException extends StoryVaultException
{
    /**
     * @var int Remaining seconds until next post is allowed
     */
    private $remainingSeconds;

    /**
     * @var int Unix timestamp when next post is allowed
     */
    private $nextPostTime;

    public function __construct(
        $remainingSeconds,
        $nextPostTime,
        $message = "Rate limit exceeded",
        $code = 0,
        \Exception $previous = null
    ) {
        $this->remainingSeconds = $remainingSeconds;
        $this->nextPostTime = $nextPostTime;

        $nextTimeFormatted = date('H:i:s', $nextPostTime);
        $userMessage = "Вы можете отправить следующее сообщение через {$remainingSeconds} секунд (в {$nextTimeFormatted})";

        parent::__construct($message, $userMessage, $code, $previous);
    }

    /**
     * Get remaining seconds
     *
     * @return int
     */
    public function getRemainingSeconds()
    {
        return $this->remainingSeconds;
    }

    /**
     * Get next post time
     *
     * @return int
     */
    public function getNextPostTime()
    {
        return $this->nextPostTime;
    }
}
