<?php

namespace tests\unit\services;

use app\services\PostManagementService;
use app\models\PostForm;
use app\models\PostEditForm;
use app\models\Author;
use app\models\Post;
use Codeception\Test\Unit;

class PostManagementServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * @var PostManagementService
     */
    private $service;

    protected function _before()
    {
        $this->service = new PostManagementService();
    }

    /**
     * Test successful post creation
     */
    public function testCreatePostSuccess()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'testuser@example.com';
        $form->message = 'This is a test message for post creation';
        $form->verifyCode = 'testcode';

        $result = $this->service->createPost($form);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['post']);
        $this->assertInstanceOf(Post::class, $result['post']);
        $this->assertEquals('This is a test message for post creation', $result['post']->message);
        $this->assertNotNull($result['post']->edit_token);
        $this->assertNotNull($result['post']->delete_token);
    }

    /**
     * Test post creation creates author if doesn't exist
     */
    public function testCreatePostCreatesAuthor()
    {
        $form = new PostForm();
        $form->author = 'New Author';
        $form->email = 'newauthor@example.com';
        $form->message = 'First post from new author';
        $form->verifyCode = 'testcode';

        $result = $this->service->createPost($form);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['post']);
        $this->assertNotNull($result['post']->author);
        $this->assertEquals('New Author', $result['post']->author->name);
        $this->assertEquals('newauthor@example.com', $result['post']->author->email);
    }

    /**
     * Test post creation updates existing author
     */
    public function testCreatePostUpdatesExistingAuthor()
    {
        // Create initial author
        $author = new Author();
        $author->email = 'existing@example.com';
        $author->name = 'Old Name';
        $author->ip_address = '192.168.1.1';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $form = new PostForm();
        $form->author = 'Updated Name';
        $form->email = 'existing@example.com';
        $form->message = 'Post from existing author';
        $form->verifyCode = 'testcode';

        $result = $this->service->createPost($form);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['post']);

        // Reload author to get fresh data
        $updatedAuthor = Author::findOne(['email' => 'existing@example.com']);
        $this->assertEquals('Updated Name', $updatedAuthor->name);
    }

    /**
     * Test rate limit prevents rapid posting
     */
    public function testCreatePostRateLimitPreventsRapidPosting()
    {
        // Create author with recent post
        $author = new Author();
        $author->email = 'ratelimit@example.com';
        $author->name = 'Rate Limit User';
        $author->ip_address = '192.168.1.2';
        $author->created_at = time();
        $author->updated_at = time();
        $author->last_post_at = time(); // Just posted
        $author->save(false);

        // Try to post immediately (should fail rate limit)
        $form = new PostForm();
        $form->author = 'Rate Limit User';
        $form->email = 'ratelimit@example.com';
        $form->message = 'Trying to post too soon';
        $form->verifyCode = 'testcode';

        $result = $this->service->createPost($form);

        $this->assertFalse($result['success']);
        $this->assertNull($result['post']);
        $this->assertStringContainsString('часов', $result['message']);
    }

    /**
     * Test getPostsList returns active posts
     */
    public function testGetPostsListReturnsActivePosts()
    {
        $result = $this->service->getPostsList(10);

        $this->assertArrayHasKey('posts', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertIsArray($result['posts']);
    }

    /**
     * Test updatePostByToken with valid token and within time limit
     */
    public function testUpdatePostByTokenSuccess()
    {
        // Create a post
        $author = new Author();
        $author->email = 'edit@example.com';
        $author->name = 'Edit User';
        $author->ip_address = '192.168.1.3';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Original message';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        // Update the post
        $editForm = new PostEditForm();
        $editForm->message = 'Updated message';

        $result = $this->service->updatePostByToken($post->edit_token, $editForm);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['post']);
        $this->assertEquals('Updated message', $result['post']->message);
    }

    /**
     * Test updatePostByToken with invalid token
     */
    public function testUpdatePostByTokenWithInvalidToken()
    {
        $editForm = new PostEditForm();
        $editForm->message = 'Updated message';

        $result = $this->service->updatePostByToken('invalid_token_12345', $editForm);

        $this->assertFalse($result['success']);
        $this->assertNull($result['post']);
        $this->assertStringContainsString('не найден', $result['message']);
    }

    /**
     * Test updatePostByToken fails when edit time expired (>12 hours)
     */
    public function testUpdatePostByTokenFailsWhenExpired()
    {
        // Create a post older than 12 hours
        $author = new Author();
        $author->email = 'expired@example.com';
        $author->name = 'Expired User';
        $author->ip_address = '192.168.1.4';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Old message';
        $post->created_at = time() - (13 * 3600); // 13 hours ago
        $post->updated_at = time() - (13 * 3600);
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $editForm = new PostEditForm();
        $editForm->message = 'Trying to update old post';

        $result = $this->service->updatePostByToken($post->edit_token, $editForm);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('истекло', $result['message']);
    }

    /**
     * Test deletePostByToken success
     */
    public function testDeletePostByTokenSuccess()
    {
        // Create a post
        $author = new Author();
        $author->email = 'delete@example.com';
        $author->name = 'Delete User';
        $author->ip_address = '192.168.1.5';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'To be deleted';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->deletePostByToken($post->delete_token);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('удален', $result['message']);

        // Verify post is soft deleted
        $deletedPost = Post::findOne($post->id);
        $this->assertNotNull($deletedPost->deleted_at);
    }

    /**
     * Test deletePostByToken with invalid token
     */
    public function testDeletePostByTokenWithInvalidToken()
    {
        $result = $this->service->deletePostByToken('invalid_delete_token');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('не найден', $result['message']);
    }

    /**
     * Test deletePostByToken fails when delete time expired (>14 days)
     */
    public function testDeletePostByTokenFailsWhenExpired()
    {
        // Create a post older than 14 days
        $author = new Author();
        $author->email = 'olddelete@example.com';
        $author->name = 'Old Delete User';
        $author->ip_address = '192.168.1.6';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Very old message';
        $post->created_at = time() - (15 * 24 * 3600); // 15 days ago
        $post->updated_at = time() - (15 * 24 * 3600);
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->deletePostByToken($post->delete_token);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('истекло', $result['message']);
    }

    /**
     * Test getPostForEdit returns post when valid
     */
    public function testGetPostForEditReturnsPostWhenValid()
    {
        // Create a recent post
        $author = new Author();
        $author->email = 'getedit@example.com';
        $author->name = 'Get Edit User';
        $author->ip_address = '192.168.1.7';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Editable post';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->getPostForEdit($post->edit_token);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals($post->id, $result->id);
    }

    /**
     * Test getPostForEdit returns null when expired
     */
    public function testGetPostForEditReturnsNullWhenExpired()
    {
        // Create an old post
        $author = new Author();
        $author->email = 'oldgetedit@example.com';
        $author->name = 'Old Get Edit User';
        $author->ip_address = '192.168.1.8';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Old editable post';
        $post->created_at = time() - (13 * 3600); // 13 hours ago
        $post->updated_at = time() - (13 * 3600);
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->getPostForEdit($post->edit_token);

        $this->assertNull($result);
    }

    /**
     * Test getPostForDelete returns post when valid
     */
    public function testGetPostForDeleteReturnsPostWhenValid()
    {
        // Create a recent post
        $author = new Author();
        $author->email = 'getdelete@example.com';
        $author->name = 'Get Delete User';
        $author->ip_address = '192.168.1.9';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Deletable post';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->getPostForDelete($post->delete_token);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals($post->id, $result->id);
    }

    /**
     * Test getPostForDelete returns null when expired
     */
    public function testGetPostForDeleteReturnsNullWhenExpired()
    {
        // Create an old post (>14 days)
        $author = new Author();
        $author->email = 'oldgetdelete@example.com';
        $author->name = 'Old Get Delete User';
        $author->ip_address = '192.168.1.10';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Old deletable post';
        $post->created_at = time() - (15 * 24 * 3600); // 15 days ago
        $post->updated_at = time() - (15 * 24 * 3600);
        $post->edit_token = bin2hex(random_bytes(32));
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $result = $this->service->getPostForDelete($post->delete_token);

        $this->assertNull($result);
    }

    /**
     * Test getPostForEdit returns null for invalid token
     */
    public function testGetPostForEditReturnsNullForInvalidToken()
    {
        $result = $this->service->getPostForEdit('totally_invalid_token');
        $this->assertNull($result);
    }

    /**
     * Test getPostForDelete returns null for invalid token
     */
    public function testGetPostForDeleteReturnsNullForInvalidToken()
    {
        $result = $this->service->getPostForDelete('totally_invalid_token');
        $this->assertNull($result);
    }
}
