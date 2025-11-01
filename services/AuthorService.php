<?php

namespace app\services;

use app\models\Author;
use app\repositories\AuthorRepository;

/**
 * AuthorService handles business logic for authors
 */
class AuthorService
{
    /**
     * @var AuthorRepository
     */
    private $repository;

    /**
     * Rate limit: 1 post per 3 minutes (180 seconds)
     */
    const RATE_LIMIT_SECONDS = 180;

    public function __construct()
    {
        $this->repository = new AuthorRepository();
    }

    /**
     * Get or create author
     *
     * @param string $email
     * @param string $name
     * @param string $ip
     * @return Author
     */
    public function getOrCreateAuthor($email, $name, $ip)
    {
        return $this->repository->findOrCreate($email, $name, $ip);
    }

    /**
     * Check if author can post now (rate limiting)
     *
     * @param Author $author
     * @return bool
     */
    public function canPostNow(Author $author)
    {
        if ($author->last_post_at === null) {
            return true;
        }

        $elapsed = time() - $author->last_post_at;
        return $elapsed >= self::RATE_LIMIT_SECONDS;
    }

    /**
     * Get next allowed post time (Unix timestamp)
     *
     * @param Author $author
     * @return int
     */
    public function getNextPostTime(Author $author)
    {
        if ($author->last_post_at === null) {
            return time();
        }

        return $author->last_post_at + self::RATE_LIMIT_SECONDS;
    }

    /**
     * Get remaining seconds until next post is allowed
     *
     * @param Author $author
     * @return int
     */
    public function getRemainingSeconds(Author $author)
    {
        $nextTime = $this->getNextPostTime($author);
        $remaining = $nextTime - time();
        return max(0, $remaining);
    }

    /**
     * Save author
     *
     * @param Author $author
     * @return bool
     */
    public function save(Author $author)
    {
        return $this->repository->save($author);
    }

    /**
     * Update author's last post timestamp
     *
     * @param Author $author
     * @return bool
     */
    public function updateLastPost(Author $author)
    {
        return $this->repository->updateLastPost($author);
    }
}
