<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%authors}}`.
 */
class m250101_000001_create_authors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%authors}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string(255)->notNull()->unique()->comment('Unique email identifier'),
            'name' => $this->string(15)->notNull()->comment('Current author name'),
            'ip_address' => $this->string(45)->notNull()->comment('Current IP address (supports IPv6)'),
            'created_at' => $this->integer()->notNull()->comment('Unix timestamp of registration'),
            'updated_at' => $this->integer()->notNull()->comment('Unix timestamp of last update'),
            'last_post_at' => $this->integer()->null()->comment('Unix timestamp of last post for rate limiting'),
        ]);

        // Create indexes for better performance
        $this->createIndex(
            'idx-authors-email',
            '{{%authors}}',
            'email',
            true // unique
        );

        $this->createIndex(
            'idx-authors-last_post_at',
            '{{%authors}}',
            'last_post_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%authors}}');
    }
}
