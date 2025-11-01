<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Author model
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $ip_address
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $last_post_at
 *
 * @property Post[] $posts
 * @property int $postsCount
 * @property int $postsCountByIp
 */
class Author extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%authors}}';
    }

    /**
     * Get relation to posts
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['author_id' => 'id']);
    }

    /**
     * Get active (not deleted) posts relation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivePosts()
    {
        return $this->hasMany(Post::class, ['author_id' => 'id'])
            ->andWhere(['deleted_at' => null]);
    }

    /**
     * Get count of active posts for this author
     *
     * @return int
     */
    public function getPostsCount()
    {
        return $this->getActivePosts()->count();
    }

    /**
     * Get count of all active posts from this IP address
     *
     * Counts all posts from all authors with the same IP address
     *
     * @return int
     */
    public function getPostsCountByIp()
    {
        $repository = new \app\repositories\PostRepository();
        return $repository->countByIp($this->ip_address);
    }
}
