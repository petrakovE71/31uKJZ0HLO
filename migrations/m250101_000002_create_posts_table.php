<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%posts}}`.
 * Has foreign keys to the table `{{%authors}}`.
 */
class m250101_000002_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull()->comment('Foreign key to authors table'),
            'message' => $this->text()->notNull()->comment('Post message content'),
            'created_at' => $this->integer()->notNull()->comment('Unix timestamp of post creation'),
            'updated_at' => $this->integer()->notNull()->comment('Unix timestamp of last update'),
            'deleted_at' => $this->integer()->null()->comment('Unix timestamp of soft deletion'),
            'edit_token' => $this->string(64)->notNull()->unique()->comment('Unique token for edit link'),
            'delete_token' => $this->string(64)->notNull()->unique()->comment('Unique token for delete link'),
        ]);

        // Create indexes for better performance
        $this->createIndex(
            'idx-posts-author_id',
            '{{%posts}}',
            'author_id'
        );

        $this->createIndex(
            'idx-posts-created_at',
            '{{%posts}}',
            'created_at'
        );

        $this->createIndex(
            'idx-posts-deleted_at',
            '{{%posts}}',
            'deleted_at'
        );

        $this->createIndex(
            'idx-posts-edit_token',
            '{{%posts}}',
            'edit_token',
            true // unique
        );

        $this->createIndex(
            'idx-posts-delete_token',
            '{{%posts}}',
            'delete_token',
            true // unique
        );

        // Add foreign key for author_id
        $this->addForeignKey(
            'fk-posts-author_id',
            '{{%posts}}',
            'author_id',
            '{{%authors}}',
            'id',
            'CASCADE' // Delete posts when author is deleted
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-posts-author_id', '{{%posts}}');

        // Then drop table
        $this->dropTable('{{%posts}}');
    }
}
