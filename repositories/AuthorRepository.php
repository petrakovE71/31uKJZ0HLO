<?php

namespace app\repositories;

use Yii;
use app\models\Author;
use yii\db\Exception as DbException;

/**
 * AuthorRepository handles data access for Author model with error handling
 */
class AuthorRepository
{
    /**
     * Find author by email with error handling
     *
     * @param string $email
     * @return Author|null
     */
    public function findByEmail($email)
    {
        if (empty($email)) {
            Yii::warning('Empty email provided to findByEmail', __METHOD__);
            return null;
        }

        try {
            return Author::findOne(['email' => $email]);

        } catch (\Exception $e) {
            Yii::error("Failed to find author by email: {$e->getMessage()}", __METHOD__);
            return null;
        }
    }

    /**
     * Find or create author with race condition protection
     *
     * Handles concurrent requests for the same email by using database-level
     * UNIQUE constraint. If insertion fails due to duplicate key, retries find.
     *
     * @param string $email
     * @param string $name
     * @param string $ip
     * @return Author|null Returns null on failure
     */
    public function findOrCreate($email, $name, $ip)
    {
        if (empty($email)) {
            Yii::error('Empty email provided to findOrCreate', __METHOD__);
            return null;
        }

        try {
            // First attempt: find existing author
            $author = $this->findByEmail($email);

            if ($author !== null) {
                // Author exists - update name and IP
                $author->name = $name;
                $author->ip_address = $ip;
                $author->updated_at = time();
                return $author;
            }

            // Author doesn't exist - create new one
            $author = new Author();
            $author->email = $email;
            $author->name = $name;
            $author->ip_address = $ip;
            $author->created_at = time();
            $author->updated_at = time();

            // Try to save - may fail on duplicate key due to race condition
            if ($author->save()) {
                return $author;
            }

            // Save failed - check if it's due to duplicate email (race condition)
            $errors = $author->getErrors();
            if (isset($errors['email'])) {
                // Race condition detected - another request created this author
                // Retry finding the author
                Yii::info("Race condition detected for email {$email}, retrying find", __METHOD__);
                $author = $this->findByEmail($email);

                if ($author !== null) {
                    // Found it - update and return
                    $author->name = $name;
                    $author->ip_address = $ip;
                    $author->updated_at = time();
                    return $author;
                }
            }

            // Unexpected save failure
            Yii::error("Failed to create author for {$email}: " . json_encode($errors), __METHOD__);
            return null;

        } catch (DbException $e) {
            // Database exception - could be duplicate key or other DB error
            Yii::error("Database error in findOrCreate: {$e->getMessage()}", __METHOD__);

            // Try one more time to find the author in case it was created by concurrent request
            try {
                $author = $this->findByEmail($email);
                if ($author !== null) {
                    $author->name = $name;
                    $author->ip_address = $ip;
                    $author->updated_at = time();
                    return $author;
                }
            } catch (\Exception $retryException) {
                Yii::error("Retry failed in findOrCreate: {$retryException->getMessage()}", __METHOD__);
            }

            return null;

        } catch (\Exception $e) {
            Yii::error("Unexpected error in findOrCreate: {$e->getMessage()}", __METHOD__);
            return null;
        }
    }

    /**
     * Update author's last post timestamp with error handling
     *
     * @param Author $author
     * @return bool
     */
    public function updateLastPost(Author $author)
    {
        if (!$author || !$author->id) {
            Yii::error('Invalid author or unsaved author in updateLastPost', __METHOD__);
            return false;
        }

        try {
            $author->last_post_at = time();
            return $author->save(false);

        } catch (\Exception $e) {
            Yii::error("Failed to update last post timestamp for author ID {$author->id}: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }

    /**
     * Save author with error handling
     *
     * @param Author $author
     * @return bool
     */
    public function save(Author $author)
    {
        if (!$author) {
            Yii::error('Invalid author object in save', __METHOD__);
            return false;
        }

        try {
            $result = $author->save();

            if (!$result) {
                $errors = $author->getErrors();
                Yii::error('Failed to save author: ' . json_encode($errors), __METHOD__);
            }

            return $result;

        } catch (DbException $e) {
            Yii::error("Database error saving author: {$e->getMessage()}", __METHOD__);
            return false;

        } catch (\Exception $e) {
            Yii::error("Unexpected error saving author: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }
}
