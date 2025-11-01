<?php

namespace app\services;

use Yii;
use app\models\Post;
use app\models\PostForm;
use app\models\PostEditForm;
use app\exceptions\PostCreationException;
use app\exceptions\RateLimitException;
use app\exceptions\EmailNotificationException;

/**
 * PostManagementService - Facade for managing complete post lifecycle
 *
 * This service coordinates all operations related to posts with full error handling:
 * - Creation (with author management, rate limiting, email notifications)
 * - Updates (with permission checks)
 * - Deletion (with permission checks)
 * - Retrieval for display
 *
 * Features:
 * - Database transactions for atomicity
 * - Exception handling with logging
 * - Graceful degradation (email failure doesn't break post creation)
 * - Fallback mechanisms
 *
 * Follows Facade Pattern to provide simple interface for complex subsystems.
 */
class PostManagementService
{
    private $authorService;
    private $postService;
    private $emailService;
    private $ipService;
    private $postRepository;

    public function __construct()
    {
        $this->authorService = new AuthorService();
        $this->postService = new PostService();
        $this->emailService = new EmailService();
        $this->ipService = new IpService();
        $this->postRepository = new \app\repositories\PostRepository();
    }

    /**
     * Create new post with full business logic and error handling
     *
     * Process:
     * 1. Get current IP (with fallback)
     * 2. Get or create author (in transaction)
     * 3. Check rate limit (throws exception if exceeded)
     * 4. Save author (in transaction)
     * 5. Create and save post (in transaction)
     * 6. Update author timestamp (in transaction)
     * 7. Commit transaction
     * 8. Send email notification (graceful failure)
     *
     * @param PostForm $form Validated form model
     * @return array ['success' => bool, 'message' => string, 'post' => Post|null]
     */
    public function createPost(PostForm $form)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // 1. Get current IP address (with fallback)
            $ip = $this->getIpSafely();

            // 2. Get or create author (can throw DB exception)
            $author = $this->authorService->getOrCreateAuthor(
                $form->email,
                $form->author,
                $ip
            );

            // 3. Check rate limit (throws RateLimitException)
            if (!$this->authorService->canPostNow($author)) {
                $remaining = $this->authorService->getRemainingSeconds($author);
                $nextTime = $this->authorService->getNextPostTime($author);
                throw new RateLimitException($remaining, $nextTime);
            }

            // 4. Save author
            if (!$this->authorService->save($author)) {
                throw new PostCreationException('Author save failed');
            }

            // 5. Create post
            $post = $this->postService->createPost($form, $author);

            // 6. Save post
            if (!$this->postService->save($post)) {
                throw new PostCreationException('Post save failed');
            }

            // 7. Update author's last post timestamp (critical for rate limiting)
            if (!$this->authorService->updateLastPost($author)) {
                throw new PostCreationException('Author timestamp update failed');
            }

            // Commit transaction - all DB operations succeeded
            $transaction->commit();

            // 8. Send email notification (NOT in transaction - graceful degradation)
            $this->sendEmailSafely($post, $author);

            return [
                'success' => true,
                'message' => 'Ваше сообщение успешно опубликовано! Проверьте email для управления постом.',
                'post' => $post,
            ];

        } catch (RateLimitException $e) {
            // Rate limit exceeded - rollback and return user message
            $transaction->rollBack();
            Yii::info('Rate limit exceeded: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'message' => $e->getUserMessage(),
                'post' => null,
            ];

        } catch (PostCreationException $e) {
            // Post creation failed - rollback, log technical error, return user message
            $transaction->rollBack();
            Yii::error($e->getMessage() . ' | ' . $e->getTraceAsString(), __METHOD__);

            return [
                'success' => false,
                'message' => $e->getUserMessage(),
                'post' => null,
            ];

        } catch (\Exception $e) {
            // Unexpected error - rollback, log, return generic message
            $transaction->rollBack();
            Yii::error('Unexpected error in createPost: ' . $e->getMessage() . ' | ' . $e->getTraceAsString(), __METHOD__);

            return [
                'success' => false,
                'message' => 'Произошла системная ошибка. Попробуйте позже.',
                'post' => null,
            ];
        }
    }

    /**
     * Update post by edit token
     *
     * @param string $token Edit token
     * @param PostEditForm $form Validated form model
     * @return array ['success' => bool, 'message' => string, 'post' => Post|null]
     */
    public function updatePostByToken($token, PostEditForm $form)
    {
        try {
            // Find post
            $post = $this->postService->findByEditToken($token);

            if ($post === null) {
                Yii::info("Post not found for edit token: {$token}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Пост не найден или уже удален',
                    'post' => null,
                ];
            }

            // Check if edit time has expired (12 hours)
            if (!$this->postService->canEdit($post)) {
                Yii::info("Edit time expired for post ID: {$post->id}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Время для редактирования истекло (доступно только 12 часов после публикации)',
                    'post' => $post,
                ];
            }

            // Update post
            if (!$this->postService->updatePost($post, $form)) {
                Yii::error("Failed to update post ID: {$post->id}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Ошибка при обновлении поста',
                    'post' => $post,
                ];
            }

            Yii::info("Post updated successfully ID: {$post->id}", __METHOD__);
            return [
                'success' => true,
                'message' => 'Пост успешно обновлен',
                'post' => $post,
            ];

        } catch (\Exception $e) {
            // Catch unexpected errors during post update
            Yii::error('Unexpected error in updatePostByToken: ' . $e->getMessage() . ' | ' . $e->getTraceAsString(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Произошла системная ошибка при обновлении. Попробуйте позже.',
                'post' => null,
            ];
        }
    }

    /**
     * Delete post by delete token
     *
     * @param string $token Delete token
     * @return array ['success' => bool, 'message' => string]
     */
    public function deletePostByToken($token)
    {
        try {
            // Find post
            $post = $this->postService->findByDeleteToken($token);

            if ($post === null) {
                Yii::info("Post not found for delete token: {$token}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Пост не найден или уже удален',
                ];
            }

            // Check if delete time has expired (14 days)
            if (!$this->postService->canDelete($post)) {
                Yii::info("Delete time expired for post ID: {$post->id}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Время для удаления истекло (доступно только 14 дней после публикации)',
                ];
            }

            // Soft delete
            if (!$this->postService->softDeletePost($post)) {
                Yii::error("Failed to delete post ID: {$post->id}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Ошибка при удалении поста',
                ];
            }

            Yii::info("Post deleted successfully ID: {$post->id}", __METHOD__);
            return [
                'success' => true,
                'message' => 'Пост успешно удален',
            ];

        } catch (\Exception $e) {
            // Catch unexpected errors during post deletion
            Yii::error('Unexpected error in deletePostByToken: ' . $e->getMessage() . ' | ' . $e->getTraceAsString(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Произошла системная ошибка при удалении. Попробуйте позже.',
            ];
        }
    }

    /**
     * Get post for editing (with permission check)
     *
     * @param string $token Edit token
     * @return Post|null
     */
    public function getPostForEdit($token)
    {
        $post = $this->postService->findByEditToken($token);

        if ($post === null) {
            return null;
        }

        // Check if edit time has expired
        if (!$this->postService->canEdit($post)) {
            return null;
        }

        return $post;
    }

    /**
     * Get post for deletion (with permission check)
     *
     * @param string $token Delete token
     * @return Post|null
     */
    public function getPostForDelete($token)
    {
        $post = $this->postService->findByDeleteToken($token);

        if ($post === null) {
            return null;
        }

        // Check if delete time has expired
        if (!$this->postService->canDelete($post)) {
            return null;
        }

        return $post;
    }

    /**
     * Get posts list for display
     *
     * @param int $pageSize Posts per page
     * @return array ['posts' => Post[], 'pagination' => Pagination, 'totalCount' => int]
     */
    public function getPostsList($pageSize = 20)
    {
        return $this->postRepository->findAllActive($pageSize);
    }

    /**
     * Send email notification with graceful failure handling
     *
     * Email failures should NOT break post creation - log and continue
     *
     * @param Post $post
     * @param Author $author
     * @return void
     */
    private function sendEmailSafely($post, $author)
    {
        try {
            $this->emailService->sendPostCreatedEmail($post, $author);
        } catch (EmailNotificationException $e) {
            // Log email failure but don't throw - graceful degradation
            Yii::warning('Email notification failed: ' . $e->getMessage(), __METHOD__);
        } catch (\Exception $e) {
            // Log unexpected email error but don't throw
            Yii::warning('Unexpected error sending email: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Get IP address with fallback to prevent blocking post creation
     *
     * @return string IP address or '0.0.0.0' if detection fails
     */
    private function getIpSafely()
    {
        try {
            return $this->ipService->getCurrentIp();
        } catch (\Exception $e) {
            // Log IP detection failure and fallback to default
            Yii::warning('IP detection failed, using fallback: ' . $e->getMessage(), __METHOD__);
            return '0.0.0.0';
        }
    }
}
