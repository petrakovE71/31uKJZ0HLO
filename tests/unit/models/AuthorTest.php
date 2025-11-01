<?php

namespace tests\unit\models;

use app\models\Author;
use app\models\Post;
use Codeception\Test\Unit;

class AuthorTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * Test getPosts() relation returns all posts
     */
    public function testGetPostsReturnsAllPosts()
    {
        $author = new Author();
        $author->email = 'posts@test.com';
        $author->name = 'Posts User';
        $author->ip_address = '192.168.1.200';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create active post
        $post1 = new Post();
        $post1->author_id = $author->id;
        $post1->message = 'Active post';
        $post1->created_at = time();
        $post1->updated_at = time();
        $post1->edit_token = bin2hex(random_bytes(32));
        $post1->delete_token = bin2hex(random_bytes(32));
        $post1->save(false);

        // Create deleted post
        $post2 = new Post();
        $post2->author_id = $author->id;
        $post2->message = 'Deleted post';
        $post2->created_at = time();
        $post2->updated_at = time();
        $post2->deleted_at = time();
        $post2->edit_token = bin2hex(random_bytes(32));
        $post2->delete_token = bin2hex(random_bytes(32));
        $post2->save(false);

        // getPosts() should return both active and deleted
        $posts = $author->posts;
        $this->assertCount(2, $posts);
    }

    /**
     * Test getActivePosts() relation returns only active posts
     */
    public function testGetActivePostsReturnsOnlyActivePosts()
    {
        $author = new Author();
        $author->email = 'active@test.com';
        $author->name = 'Active User';
        $author->ip_address = '192.168.1.201';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create active post
        $post1 = new Post();
        $post1->author_id = $author->id;
        $post1->message = 'Active post';
        $post1->created_at = time();
        $post1->updated_at = time();
        $post1->edit_token = bin2hex(random_bytes(32));
        $post1->delete_token = bin2hex(random_bytes(32));
        $post1->save(false);

        // Create deleted post
        $post2 = new Post();
        $post2->author_id = $author->id;
        $post2->message = 'Deleted post';
        $post2->created_at = time();
        $post2->updated_at = time();
        $post2->deleted_at = time();
        $post2->edit_token = bin2hex(random_bytes(32));
        $post2->delete_token = bin2hex(random_bytes(32));
        $post2->save(false);

        // getActivePosts() should return only active
        $activePosts = $author->activePosts;
        $this->assertCount(1, $activePosts);
        $this->assertEquals($post1->id, $activePosts[0]->id);
        $this->assertNull($activePosts[0]->deleted_at);
    }

    /**
     * Test getPostsCount() returns count of active posts only
     */
    public function testGetPostsCountReturnsActivePostsCount()
    {
        $author = new Author();
        $author->email = 'count@test.com';
        $author->name = 'Count User';
        $author->ip_address = '192.168.1.202';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create 3 active posts
        for ($i = 0; $i < 3; $i++) {
            $post = new Post();
            $post->author_id = $author->id;
            $post->message = "Active post $i";
            $post->created_at = time();
            $post->updated_at = time();
            $post->edit_token = bin2hex(random_bytes(32));
            $post->delete_token = bin2hex(random_bytes(32));
            $post->save(false);
        }

        // Create 2 deleted posts
        for ($i = 0; $i < 2; $i++) {
            $post = new Post();
            $post->author_id = $author->id;
            $post->message = "Deleted post $i";
            $post->created_at = time();
            $post->updated_at = time();
            $post->deleted_at = time();
            $post->edit_token = bin2hex(random_bytes(32));
            $post->delete_token = bin2hex(random_bytes(32));
            $post->save(false);
        }

        // Reload author to clear any cached relations
        $author = Author::findOne($author->id);

        // Should count only active posts
        $this->assertEquals(3, $author->postsCount);
    }

    /**
     * Test getPostsCountByIp() returns count from all authors with same IP
     */
    public function testGetPostsCountByIpReturnsCountFromAllAuthorsWithSameIp()
    {
        $ip = '192.168.1.203';

        // Create first author
        $author1 = new Author();
        $author1->email = 'user1@test.com';
        $author1->name = 'User One';
        $author1->ip_address = $ip;
        $author1->created_at = time();
        $author1->updated_at = time();
        $author1->save(false);

        // Create second author with same IP
        $author2 = new Author();
        $author2->email = 'user2@test.com';
        $author2->name = 'User Two';
        $author2->ip_address = $ip;
        $author2->created_at = time();
        $author2->updated_at = time();
        $author2->save(false);

        // Create 2 posts for author1
        for ($i = 0; $i < 2; $i++) {
            $post = new Post();
            $post->author_id = $author1->id;
            $post->message = "Author1 post $i";
            $post->created_at = time();
            $post->updated_at = time();
            $post->edit_token = bin2hex(random_bytes(32));
            $post->delete_token = bin2hex(random_bytes(32));
            $post->save(false);
        }

        // Create 3 posts for author2
        for ($i = 0; $i < 3; $i++) {
            $post = new Post();
            $post->author_id = $author2->id;
            $post->message = "Author2 post $i";
            $post->created_at = time();
            $post->updated_at = time();
            $post->edit_token = bin2hex(random_bytes(32));
            $post->delete_token = bin2hex(random_bytes(32));
            $post->save(false);
        }

        // Reload authors
        $author1 = Author::findOne($author1->id);
        $author2 = Author::findOne($author2->id);

        // Both should return total count for the IP (2 + 3 = 5)
        $this->assertEquals(5, $author1->postsCountByIp);
        $this->assertEquals(5, $author2->postsCountByIp);
    }

    /**
     * Test getPostsCountByIp() excludes deleted posts
     */
    public function testGetPostsCountByIpExcludesDeletedPosts()
    {
        $ip = '192.168.1.204';

        $author = new Author();
        $author->email = 'ipdeleted@test.com';
        $author->name = 'IP Deleted User';
        $author->ip_address = $ip;
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Create 2 active posts
        for ($i = 0; $i < 2; $i++) {
            $post = new Post();
            $post->author_id = $author->id;
            $post->message = "Active post $i";
            $post->created_at = time();
            $post->updated_at = time();
            $post->edit_token = bin2hex(random_bytes(32));
            $post->delete_token = bin2hex(random_bytes(32));
            $post->save(false);
        }

        // Create 1 deleted post
        $deletedPost = new Post();
        $deletedPost->author_id = $author->id;
        $deletedPost->message = 'Deleted post';
        $deletedPost->created_at = time();
        $deletedPost->updated_at = time();
        $deletedPost->deleted_at = time();
        $deletedPost->edit_token = bin2hex(random_bytes(32));
        $deletedPost->delete_token = bin2hex(random_bytes(32));
        $deletedPost->save(false);

        // Reload author
        $author = Author::findOne($author->id);

        // Should count only active posts
        $this->assertEquals(2, $author->postsCountByIp);
    }

    /**
     * Test tableName() returns correct table
     */
    public function testTableNameReturnsCorrectTable()
    {
        $tableName = Author::tableName();
        $this->assertEquals('{{%authors}}', $tableName);
    }

    /**
     * Test author can be saved with all required fields
     */
    public function testAuthorCanBeSavedWithRequiredFields()
    {
        $author = new Author();
        $author->email = 'save@test.com';
        $author->name = 'Save Test';
        $author->ip_address = '192.168.1.205';
        $author->created_at = time();
        $author->updated_at = time();

        $result = $author->save(false);

        $this->assertTrue($result);
        $this->assertNotNull($author->id);

        // Verify it was saved
        $savedAuthor = Author::findOne($author->id);
        $this->assertNotNull($savedAuthor);
        $this->assertEquals('save@test.com', $savedAuthor->email);
        $this->assertEquals('Save Test', $savedAuthor->name);
    }

    /**
     * Test last_post_at can be null
     */
    public function testLastPostAtCanBeNull()
    {
        $author = new Author();
        $author->email = 'lastpost@test.com';
        $author->name = 'Last Post Test';
        $author->ip_address = '192.168.1.206';
        $author->created_at = time();
        $author->updated_at = time();
        $author->last_post_at = null;
        $author->save(false);

        $this->assertNull($author->last_post_at);

        // Verify from database
        $savedAuthor = Author::findOne($author->id);
        $this->assertNull($savedAuthor->last_post_at);
    }

    /**
     * Test last_post_at can be set to timestamp
     */
    public function testLastPostAtCanBeSetToTimestamp()
    {
        $author = new Author();
        $author->email = 'timestamp@test.com';
        $author->name = 'Timestamp Test';
        $author->ip_address = '192.168.1.207';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Set last_post_at
        $timestamp = time();
        $author->last_post_at = $timestamp;
        $author->save(false);

        $this->assertEquals($timestamp, $author->last_post_at);

        // Verify from database
        $savedAuthor = Author::findOne($author->id);
        $this->assertEquals($timestamp, $savedAuthor->last_post_at);
    }

    /**
     * Test getPostsCount() returns 0 when author has no posts
     */
    public function testGetPostsCountReturnsZeroWhenNoPostsExist()
    {
        $author = new Author();
        $author->email = 'noposts@test.com';
        $author->name = 'No Posts User';
        $author->ip_address = '192.168.1.208';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $this->assertEquals(0, $author->postsCount);
    }

    /**
     * Test getPostsCountByIp() returns 0 when no posts exist for IP
     */
    public function testGetPostsCountByIpReturnsZeroWhenNoPostsExist()
    {
        $author = new Author();
        $author->email = 'noipposts@test.com';
        $author->name = 'No IP User'; // Max 15 chars
        $author->ip_address = '192.168.1.209';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $this->assertEquals(0, $author->postsCountByIp);
    }
}
