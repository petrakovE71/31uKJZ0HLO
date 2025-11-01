<?php

namespace tests\unit\repositories;

use app\repositories\PostRepository;
use app\models\Post;
use app\models\Author;
use Codeception\Test\Unit;

class PostRepositoryTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * @var PostRepository
     */
    private $repository;

    protected function _before()
    {
        $this->repository = new PostRepository();
    }

    /**
     * Test countByIp() with valid IP address
     */
    public function testCountByIpWithValidIp()
    {
        // Create authors with same IP
        $ip = '192.168.1.1';

        $author1 = new Author();
        $author1->email = 'user1@test.com';
        $author1->name = 'User One';
        $author1->ip_address = $ip;
        $author1->created_at = time();
        $author1->updated_at = time();
        $author1->save(false);

        $author2 = new Author();
        $author2->email = 'user2@test.com';
        $author2->name = 'User Two';
        $author2->ip_address = $ip;
        $author2->created_at = time();
        $author2->updated_at = time();
        $author2->save(false);

        // Create posts for both authors
        $post1 = new Post();
        $post1->author_id = $author1->id;
        $post1->message = 'Test message 1';
        $post1->created_at = time();
        $post1->updated_at = time();
        $post1->edit_token = bin2hex(random_bytes(32));
        $post1->delete_token = bin2hex(random_bytes(32));
        $post1->save(false);

        $post2 = new Post();
        $post2->author_id = $author2->id;
        $post2->message = 'Test message 2';
        $post2->created_at = time();
        $post2->updated_at = time();
        $post2->edit_token = bin2hex(random_bytes(32));
        $post2->delete_token = bin2hex(random_bytes(32));
        $post2->save(false);

        $post3 = new Post();
        $post3->author_id = $author1->id;
        $post3->message = 'Test message 3';
        $post3->created_at = time();
        $post3->updated_at = time();
        $post3->edit_token = bin2hex(random_bytes(32));
        $post3->delete_token = bin2hex(random_bytes(32));
        $post3->save(false);

        // Count should be 3 (all posts from this IP)
        $count = $this->repository->countByIp($ip);
        $this->assertEquals(3, $count);
    }

    /**
     * Test countByIp() excludes deleted posts
     */
    public function testCountByIpExcludesDeletedPosts()
    {
        $ip = '192.168.1.2';

        $author = new Author();
        $author->email = 'user3@test.com';
        $author->name = 'User Three';
        $author->ip_address = $ip;
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        // Active post
        $post1 = new Post();
        $post1->author_id = $author->id;
        $post1->message = 'Active post';
        $post1->created_at = time();
        $post1->updated_at = time();
        $post1->edit_token = bin2hex(random_bytes(32));
        $post1->delete_token = bin2hex(random_bytes(32));
        $post1->save(false);

        // Deleted post
        $post2 = new Post();
        $post2->author_id = $author->id;
        $post2->message = 'Deleted post';
        $post2->created_at = time();
        $post2->updated_at = time();
        $post2->deleted_at = time();
        $post2->edit_token = bin2hex(random_bytes(32));
        $post2->delete_token = bin2hex(random_bytes(32));
        $post2->save(false);

        // Count should be 1 (only active post)
        $count = $this->repository->countByIp($ip);
        $this->assertEquals(1, $count);
    }

    /**
     * Test countByIp() with empty IP
     */
    public function testCountByIpWithEmptyIp()
    {
        $count = $this->repository->countByIp('');
        $this->assertEquals(0, $count);
    }

    /**
     * Test countByIp() with non-existent IP
     */
    public function testCountByIpWithNonExistentIp()
    {
        $count = $this->repository->countByIp('255.255.255.255');
        $this->assertEquals(0, $count);
    }

    /**
     * Test findAllActive() returns only active posts
     */
    public function testFindAllActiveReturnsOnlyActivePosts()
    {
        $result = $this->repository->findAllActive(10);

        $this->assertArrayHasKey('posts', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertArrayHasKey('totalCount', $result);

        // Verify all returned posts are active (deleted_at is null)
        foreach ($result['posts'] as $post) {
            $this->assertNull($post->deleted_at);
        }
    }

    /**
     * Test findByEditToken() with valid token
     */
    public function testFindByEditTokenWithValidToken()
    {
        $author = new Author();
        $author->email = 'user4@test.com';
        $author->name = 'User Four';
        $author->ip_address = '192.168.1.4';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $token = bin2hex(random_bytes(32));

        $post = new Post();
        $post->author_id = $author->id;
        $post->message = 'Test for token';
        $post->created_at = time();
        $post->updated_at = time();
        $post->edit_token = $token;
        $post->delete_token = bin2hex(random_bytes(32));
        $post->save(false);

        $foundPost = $this->repository->findByEditToken($token);

        $this->assertNotNull($foundPost);
        $this->assertEquals($post->id, $foundPost->id);
        $this->assertEquals($token, $foundPost->edit_token);
    }

    /**
     * Test findByEditToken() with invalid token
     */
    public function testFindByEditTokenWithInvalidToken()
    {
        $foundPost = $this->repository->findByEditToken('invalid_token');
        $this->assertNull($foundPost);
    }

    /**
     * Test softDelete() marks post as deleted
     */
    public function testSoftDeleteMarksPostAsDeleted()
    {
        $author = new Author();
        $author->email = 'user5@test.com';
        $author->name = 'User Five';
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

        $result = $this->repository->softDelete($post);

        $this->assertTrue($result);
        $this->assertNotNull($post->deleted_at);

        // Verify post is not returned by findAllActive
        $allActive = $this->repository->findAllActive(100);
        $activeIds = array_map(function($p) { return $p->id; }, $allActive['posts']);
        $this->assertNotContains($post->id, $activeIds);
    }
}
