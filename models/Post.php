<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Post model
 *
 * @property int $id
 * @property int $author_id
 * @property string $message
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 * @property string $edit_token
 * @property string $delete_token
 *
 * @property Author $author
 */
class Post extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%posts}}';
    }

    /**
     * Get relation to author
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Check if post is deleted (soft delete)
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    /**
     * Scope for active (not deleted) posts
     *
     * @param \yii\db\ActiveQuery $query
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return new class(get_called_class()) extends \yii\db\ActiveQuery {
            /**
             * Get only active (not deleted) posts
             */
            public function active()
            {
                return $this->andWhere(['deleted_at' => null]);
            }
        };
    }
}
