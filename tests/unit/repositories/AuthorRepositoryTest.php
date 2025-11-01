<?php

namespace tests\unit\repositories;

use app\repositories\AuthorRepository;
use app\models\Author;
use Codeception\Test\Unit;

class AuthorRepositoryTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * @var AuthorRepository
     */
    private $repository;

    protected function _before()
    {
        $this->repository = new AuthorRepository();
    }

    /**
     * Test findByEmail() with existing author
     */
    public function testFindByEmailWithExistingAuthor()
    {
        $author = new Author();
        $author->email = 'existing@test.com';
        $author->name = 'Existing User';
        $author->ip_address = '192.168.1.10';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $foundAuthor = $this->repository->findByEmail('existing@test.com');

        $this->assertNotNull($foundAuthor);
        $this->assertEquals('existing@test.com', $foundAuthor->email);
        $this->assertEquals('Existing User', $foundAuthor->name);
    }

    /**
     * Test findByEmail() with non-existent author
     */
    public function testFindByEmailWithNonExistentAuthor()
    {
        $foundAuthor = $this->repository->findByEmail('nonexistent@test.com');
        $this->assertNull($foundAuthor);
    }

    /**
     * Test findByEmail() with empty email
     */
    public function testFindByEmailWithEmptyEmail()
    {
        $foundAuthor = $this->repository->findByEmail('');
        $this->assertNull($foundAuthor);
    }

    /**
     * Test findOrCreate() creates new author when email doesn't exist
     */
    public function testFindOrCreateCreatesNewAuthor()
    {
        $author = $this->repository->findOrCreate(
            'newuser@test.com',
            'New User',
            '192.168.1.20'
        );

        $this->assertNotNull($author);
        $this->assertEquals('newuser@test.com', $author->email);
        $this->assertEquals('New User', $author->name);
        $this->assertEquals('192.168.1.20', $author->ip_address);
        $this->assertNotNull($author->created_at);
        $this->assertNotNull($author->updated_at);
    }

    /**
     * Test findOrCreate() finds existing author and updates name/IP
     */
    public function testFindOrCreateUpdatesExistingAuthor()
    {
        // Create initial author
        $initialAuthor = new Author();
        $initialAuthor->email = 'update@test.com';
        $initialAuthor->name = 'Old Name';
        $initialAuthor->ip_address = '192.168.1.30';
        $initialAuthor->created_at = time();
        $initialAuthor->updated_at = time();
        $initialAuthor->save(false);

        // FindOrCreate with new name and IP
        $author = $this->repository->findOrCreate(
            'update@test.com',
            'New Name',
            '192.168.1.31'
        );

        $this->assertNotNull($author);
        $this->assertEquals($initialAuthor->id, $author->id);
        $this->assertEquals('New Name', $author->name);
        $this->assertEquals('192.168.1.31', $author->ip_address);
    }

    /**
     * Test findOrCreate() with empty email returns null
     */
    public function testFindOrCreateWithEmptyEmail()
    {
        $author = $this->repository->findOrCreate('', 'Name', '192.168.1.40');
        $this->assertNull($author);
    }

    /**
     * Test save() successfully saves author
     */
    public function testSaveSuccessfullySavesAuthor()
    {
        $author = new Author();
        $author->email = 'tosave@test.com';
        $author->name = 'To Save';
        $author->ip_address = '192.168.1.50';
        $author->created_at = time();
        $author->updated_at = time();

        $result = $this->repository->save($author);

        $this->assertTrue($result);
        $this->assertNotNull($author->id);

        // Verify it was saved
        $savedAuthor = Author::findOne($author->id);
        $this->assertNotNull($savedAuthor);
        $this->assertEquals('tosave@test.com', $savedAuthor->email);
    }

    /**
     * Test save() with invalid author returns false
     */
    public function testSaveWithInvalidAuthorReturnsFalse()
    {
        $result = $this->repository->save(null);
        $this->assertFalse($result);
    }

    /**
     * Test updateLastPost() updates timestamp
     */
    public function testUpdateLastPostUpdatesTimestamp()
    {
        $author = new Author();
        $author->email = 'lastpost@test.com';
        $author->name = 'Last Post';
        $author->ip_address = '192.168.1.60';
        $author->created_at = time();
        $author->updated_at = time();
        $author->save(false);

        $this->assertNull($author->last_post_at);

        $result = $this->repository->updateLastPost($author);

        $this->assertTrue($result);
        $this->assertNotNull($author->last_post_at);
        $this->assertGreaterThan(0, $author->last_post_at);
    }

    /**
     * Test updateLastPost() with invalid author returns false
     */
    public function testUpdateLastPostWithInvalidAuthorReturnsFalse()
    {
        $result = $this->repository->updateLastPost(null);
        $this->assertFalse($result);
    }

    /**
     * Test updateLastPost() with unsaved author returns false
     */
    public function testUpdateLastPostWithUnsavedAuthorReturnsFalse()
    {
        $author = new Author();
        $author->email = 'unsaved@test.com';
        $author->name = 'Unsaved';
        $author->ip_address = '192.168.1.70';

        $result = $this->repository->updateLastPost($author);
        $this->assertFalse($result);
    }
}
