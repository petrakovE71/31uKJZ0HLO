<?php

namespace tests\unit\models;

use app\models\Post;
use app\models\Author;
use Codeception\Test\Unit;

class PostTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * Test getAuthor() relation returns correct author
     */
    public function testGetAuthorReturnsCorrectAuthor()
    {
        // Create author
        $author = new Author();
        $author->email = 'author@test.com';
        $author->name = 'Test Author';
        $author->ip_address = '192.168.1.100';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create post
        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Test message';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        // Test relation
        $this->assertNotNull($post->author);
        $this->assertEquals($author->id, $post->author->id);
        $this->assertEquals('Test Author', $post->author->name);
        $this->assertEquals('author@test.com', $post->author->email);
    }

    /**
     * Test isDeleted() returns false for active posts
     */
    public function testIsDeletedReturnsFalseForActivePosts()
    {
        $author = new Author();
        $author->email = 'active@test.com';
        $author->name = 'Active User';
        $author->ip_address = '192.168.1.101';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Active post';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->deleted_at = null;
        $post->save(false);

        $this->assertFalse($post->isDeleted());
        $this->assertNull($post->deleted_at);
    }

    /**
     * Test isDeleted() returns true for deleted posts
     */
    public function testIsDeletedReturnsTrueForDeletedPosts()
    {
        $author = new Author();
        $author->email = 'deleted@test.com';
        $author->name = 'Deleted User';
        $author->ip_address = '192.168.1.102';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Deleted post';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->deleted_at = time();
        $post->save(false);

        $this->assertTrue($post->isDeleted());
        $this->assertNotNull($post->deleted_at);
    }

    /**
     * Test active() scope returns only active posts
     */
    public function testActiveScopeReturnsOnlyActivePosts()
    {
        $author = new Author();
        $author->email = 'scope@test.com';
        $author->name = 'Scope User';
        $author->ip_address = '192.168.1.103';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create active post
        $activePost = new Post();
        $activePost->author_id = $author->id;
        $activePost->message = 'Active post for scope test';
        $activePost->created_at = time();
        $activePost->updated_at = time();
        $activePost->edit_token = bin2hex(random_bytes(32));
        $activePost->delete_token = bin2hex(random_bytes(32));
        $activePost->save(false);

        // Create deleted post
        $deletedPost = new Post();
        $deletedPost->author_id = $author->id;
        $deletedPost->message = 'Deleted post for scope test';
        $deletedPost->created_at = time();
        $deletedPost->updated_at = time();
        $deletedPost->deleted_at = time();
        $deletedPost->edit_token = bin2hex(random_bytes(32));
        $deletedPost->delete_token = bin2hex(random_bytes(32));
        $deletedPost->save(false);

        // Query using active scope
        $activePosts = Post::find()->active()->all();
        $activePostIds = array_map(function($p) { return $p->id; }, $activePosts);

        // Verify active post is included
        $this->assertContains($activePost->id, $activePostIds);

        // Verify deleted post is excluded
        $this->assertNotContains($deletedPost->id, $activePostIds);
    }

    /**
     * Test active() scope can be chained with other conditions
     */
    public function testActiveScopeCanBeChained()
    {
        $author = new Author();
        $author->email = 'chain@test.com';
        $author->name = 'Chain User';
        $author->ip_address = '192.168.1.104';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Chainable scope test';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        // Chain active scope with where condition
        $result = Post::find()
            ->active()
            ->where(['author_id' => $author->id])
            ->one();

        $this->assertNotNull($result);
        $this->assertEquals($post->id, $result->id);
        $this->assertFalse($result->isDeleted());
    }

    /**
     * Test tableName() returns correct table
     */
    public function testTableNameReturnsCorrectTable()
    {
        $tableName = Post::tableName();
        $this->assertEquals('{{%posts}}', $tableName);
    }

    /**
     * Test post can be saved with all required fields
     */
    public function testPostCanBeSavedWithRequiredFields()
    {
        $author = new Author();
        $author->email = 'save@test.com';
        $author->name = 'Save User';
        $author->ip_address = '192.168.1.105';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Save test message';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));

        $result = $post->save(false);

        $this->assertTrue($result);
        $this->assertNotNull($post->id);

        // Verify it was saved
        $savedPost = Post::findOne($post->id);
        $this->assertNotNull($savedPost);
        $this->assertEquals('Save test message', $savedPost->message);
    }

    /**
     * Test soft delete sets deleted_at timestamp
     */
    public function testSoftDeleteSetsDeletedAt()
    {
        $author = new Author();
        $author->email = 'softdelete@test.com';
        $author->name = 'Soft Delete User';
        $author->ip_address = '192.168.1.106';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'To be soft deleted';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $this->assertNull($post->deleted_at);
        $this->assertFalse($post->isDeleted());

        // Soft delete
        $post->deleted_at = time();
        $post->save(false);

        $this->assertNotNull($post->deleted_at);
        $this->assertTrue($post->isDeleted());
    }

    /**
     * Test tokens are unique and properly generated
     */
    public function testTokensAreUnique()
    {
        $author = new Author();
        $author->email = 'tokens@test.com';
        $author->name = 'Token User';
        $author->ip_address = '192.168.1.107';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $editToken = bin2hex(random_bytes(32));
        $deleteToken = bin2hex(random_bytes(32));

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Token test';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = $editToken;
        $post->delete_token = $deleteToken;
        $post->save(false);

        $this->assertEquals($editToken, $post->edit_token);
        $this->assertEquals($deleteToken, $post->delete_token);
        $this->assertNotEquals($editToken, $deleteToken);
        $this->assertEquals(64, strlen($editToken)); // 32 bytes = 64 hex chars
        $this->assertEquals(64, strlen($deleteToken));
    }

    /**
     * Test post with null deleted_at is considered active
     */
    public function testPostWithNullDeletedAtIsActive()
    {
        $author = new Author();
        $author->email = 'nulldeleted@test.com';
        $author->name = 'Null User';
        $author->ip_address = '192.168.1.108';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Null deleted_at test';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->deleted_at = null;
        $post->save(false);

        $foundPost = Post::find()->active()->where(['id' => $post->id])->one();

        $this->assertNotNull($foundPost);
        $this->assertEquals($post->id, $foundPost->id);
        $this->assertNull($foundPost->deleted_at);
        $this->assertFalse($foundPost->isDeleted());
    }
}
