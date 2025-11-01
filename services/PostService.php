<?php

namespace app\services;

use Yii;
use app\models\Post;
use app\models\Author;
use app\models\PostForm;
use app\models\PostEditForm;
use app\repositories\PostRepository;
use app\exceptions\PostCreationException;

/**
 * PostService handles business logic for posts with validation and error handling
 */
class PostService
{
    /**
     * @var PostRepository
     */
    private $repository;

    /**
     * Edit time limit: 12 hours
     */
    const EDIT_TIME_LIMIT = 12 * 3600; // 12 hours in seconds

    /**
     * Delete time limit: 14 days
     */
    const DELETE_TIME_LIMIT = 14 * 24 * 3600; // 14 days in seconds

    public function __construct()
    {
        $this->repository = new PostRepository();
    }

    /**
     * Create new post with validation
     *
     * @param PostForm $form
     * @param Author $author
     * @return Post
     * @throws PostCreationException
     */
    public function createPost(PostForm $form, Author $author)
    {
        // Validate input
        if (!$form || !$author) {
            throw new PostCreationException('Invalid form or author object');
        }

        if (!$author->id) {
            throw new PostCreationException('Author must be saved before creating post');
        }

        if (empty($form->message)) {
            throw new PostCreationException('Post message cannot be empty');
        }

        try {
            $post = new Post();
            $post->author_id = $author->id;
            $post->message = $form->message;
            $post->created_at = time();
            $post->updated_at = time();

            // Generate unique tokens (can throw exception if no entropy)
            $tokens = $this->generateTokens();
            $post->edit_token = $tokens['edit_token'];
            $post->delete_token = $tokens['delete_token'];

            return $post;

        } catch (PostCreationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PostCreationException(
                "Failed to create post: {$e->getMessage()}",
                'Не удалось создать пост',
                0,
                $e
            );
        }
    }

    /**
     * Update existing post with validation
     *
     * @param Post $post
     * @param PostEditForm $form
     * @return bool
     */
    public function updatePost(Post $post, PostEditForm $form)
    {
        // Validate input
        if (!$post || !$form) {
            Yii::error('Invalid post or form object in updatePost', __METHOD__);
            return false;
        }

        if (!$post->id) {
            Yii::error('Cannot update unsaved post', __METHOD__);
            return false;
        }

        if (empty($form->message)) {
            Yii::error('Post message cannot be empty', __METHOD__);
            return false;
        }

        try {
            $post->message = $form->message;
            $post->updated_at = time();

            return $this->repository->save($post);

        } catch (\Exception $e) {
            Yii::error("Failed to update post ID {$post->id}: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }

    /**
     * Soft delete post with validation
     *
     * @param Post $post
     * @return bool
     */
    public function softDeletePost(Post $post)
    {
        // Validate input
        if (!$post) {
            Yii::error('Invalid post object in softDeletePost', __METHOD__);
            return false;
        }

        if (!$post->id) {
            Yii::error('Cannot delete unsaved post', __METHOD__);
            return false;
        }

        try {
            return $this->repository->softDelete($post);

        } catch (\Exception $e) {
            Yii::error("Failed to soft delete post ID {$post->id}: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }

    /**
     * Check if post can be edited (within 12 hours)
     *
     * @param Post $post
     * @return bool
     */
    public function canEdit(Post $post)
    {
        $elapsed = time() - $post->created_at;
        return $elapsed <= self::EDIT_TIME_LIMIT;
    }

    /**
     * Check if post can be deleted (within 14 days)
     *
     * @param Post $post
     * @return bool
     */
    public function canDelete(Post $post)
    {
        $elapsed = time() - $post->created_at;
        return $elapsed <= self::DELETE_TIME_LIMIT;
    }

    /**
     * Generate unique tokens for edit and delete
     *
     * Handles random_bytes() exceptions (low entropy, system issues)
     *
     * @return array ['edit_token' => string, 'delete_token' => string]
     * @throws PostCreationException
     */
    public function generateTokens()
    {
        try {
            return [
                'edit_token' => bin2hex(random_bytes(32)), // 64 characters
                'delete_token' => bin2hex(random_bytes(32)), // 64 characters
            ];
        } catch (\Exception $e) {
            // random_bytes() can throw if not enough entropy
            Yii::error("Failed to generate random tokens: {$e->getMessage()}", __METHOD__);
            throw new PostCreationException(
                "Token generation failed: {$e->getMessage()}",
                'Не удалось создать токены для поста',
                0,
                $e
            );
        }
    }

    /**
     * Save post with validation
     *
     * @param Post $post
     * @return bool
     */
    public function save(Post $post)
    {
        // Validate input
        if (!$post) {
            Yii::error('Invalid post object in save', __METHOD__);
            return false;
        }

        try {
            return $this->repository->save($post);

        } catch (\Exception $e) {
            Yii::error("Failed to save post: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }

    /**
     * Find post by edit token with error handling
     *
     * @param string $token
     * @return Post|null
     */
    public function findByEditToken($token)
    {
        if (empty($token)) {
            Yii::warning('Empty token provided to findByEditToken', __METHOD__);
            return null;
        }

        try {
            return $this->repository->findByEditToken($token);

        } catch (\Exception $e) {
            Yii::error("Failed to find post by edit token: {$e->getMessage()}", __METHOD__);
            return null;
        }
    }

    /**
     * Find post by delete token with error handling
     *
     * @param string $token
     * @return Post|null
     */
    public function findByDeleteToken($token)
    {
        if (empty($token)) {
            Yii::warning('Empty token provided to findByDeleteToken', __METHOD__);
            return null;
        }

        try {
            return $this->repository->findByDeleteToken($token);

        } catch (\Exception $e) {
            Yii::error("Failed to find post by delete token: {$e->getMessage()}", __METHOD__);
            return null;
        }
    }
}
