<?php

namespace app\repositories;

use Yii;
use app\models\Post;
use yii\data\Pagination;
use yii\db\Exception as DbException;

/**
 * PostRepository handles data access for Post model with error handling
 */
class PostRepository
{
    /**
     * Find all active posts with pagination and fallback on error
     *
     * Returns empty array on database failure to prevent breaking the UI
     *
     * @param int $pageSize
     * @return array ['posts' => Post[], 'pagination' => Pagination, 'totalCount' => int]
     */
    public function findAllActive($pageSize = 20)
    {
        try {
            $query = Post::find()
                ->active()
                ->with('author')
                ->orderBy(['created_at' => SORT_DESC]);

            $totalCount = $query->count();

            $pagination = new Pagination([
                'defaultPageSize' => $pageSize,
                'totalCount' => $totalCount,
            ]);

            $posts = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            return [
                'posts' => $posts,
                'pagination' => $pagination,
                'totalCount' => $totalCount,
            ];

        } catch (DbException $e) {
            Yii::error("Database error in findAllActive: {$e->getMessage()}", __METHOD__);
            // Fallback: return empty array to prevent UI breaking
            return [
                'posts' => [],
                'pagination' => new Pagination(['totalCount' => 0]),
                'totalCount' => 0,
            ];

        } catch (\Exception $e) {
            Yii::error("Unexpected error in findAllActive: {$e->getMessage()}", __METHOD__);
            // Fallback: return empty array
            return [
                'posts' => [],
                'pagination' => new Pagination(['totalCount' => 0]),
                'totalCount' => 0,
            ];
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
            return Post::findOne(['edit_token' => $token, 'deleted_at' => null]);

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
            return Post::findOne(['delete_token' => $token, 'deleted_at' => null]);

        } catch (\Exception $e) {
            Yii::error("Failed to find post by delete token: {$e->getMessage()}", __METHOD__);
            return null;
        }
    }

    /**
     * Count all active posts with error handling
     *
     * @return int Returns 0 on error
     */
    public function countAll()
    {
        try {
            return Post::find()->active()->count();

        } catch (\Exception $e) {
            Yii::error("Failed to count all posts: {$e->getMessage()}", __METHOD__);
            return 0;
        }
    }

    /**
     * Count posts by author with error handling
     *
     * @param int $authorId
     * @return int Returns 0 on error
     */
    public function countByAuthor($authorId)
    {
        if (!$authorId) {
            Yii::warning('Invalid author ID in countByAuthor', __METHOD__);
            return 0;
        }

        try {
            return Post::find()->active()->where(['author_id' => $authorId])->count();

        } catch (\Exception $e) {
            Yii::error("Failed to count posts for author {$authorId}: {$e->getMessage()}", __METHOD__);
            return 0;
        }
    }

    /**
     * Count all active posts by IP address
     *
     * Counts all posts from all authors with the given IP address
     *
     * @param string $ipAddress
     * @return int Returns 0 on error
     */
    public function countByIp($ipAddress)
    {
        if (empty($ipAddress)) {
            Yii::warning('Empty IP address provided to countByIp', __METHOD__);
            return 0;
        }

        try {
            return Post::find()
                ->alias('p')
                ->innerJoin('{{%authors}} a', 'p.author_id = a.id')
                ->where(['a.ip_address' => $ipAddress])
                ->andWhere(['p.deleted_at' => null])
                ->count();

        } catch (\Exception $e) {
            Yii::error("Failed to count posts for IP {$ipAddress}: {$e->getMessage()}", __METHOD__);
            return 0;
        }
    }

    /**
     * Save post with error handling
     *
     * @param Post $post
     * @return bool
     */
    public function save(Post $post)
    {
        if (!$post) {
            Yii::error('Invalid post object in save', __METHOD__);
            return false;
        }

        try {
            $result = $post->save();

            if (!$result) {
                $errors = $post->getErrors();
                Yii::error('Failed to save post: ' . json_encode($errors), __METHOD__);
            }

            return $result;

        } catch (DbException $e) {
            Yii::error("Database error saving post: {$e->getMessage()}", __METHOD__);
            return false;

        } catch (\Exception $e) {
            Yii::error("Unexpected error saving post: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }

    /**
     * Soft delete post with error handling
     *
     * @param Post $post
     * @return bool
     */
    public function softDelete(Post $post)
    {
        if (!$post || !$post->id) {
            Yii::error('Invalid post or unsaved post in softDelete', __METHOD__);
            return false;
        }

        try {
            $post->deleted_at = time();
            return $post->save(false);

        } catch (\Exception $e) {
            Yii::error("Failed to soft delete post ID {$post->id}: {$e->getMessage()}", __METHOD__);
            return false;
        }
    }
}
