<?php

namespace tests\unit\models;

use app\models\PostForm;
use Codeception\Test\Unit;

class PostFormTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * Test validation passes with all valid data
     */
    public function testValidationPassesWithValidData()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = 'This is a valid test message';
        $form->verifyCode = 'testcode';

        // Note: In real scenario, captcha would need to be validated
        // For unit tests, we skip captcha validation
        $isValid = $form->validate(['author', 'email', 'message']);

        $this->assertTrue($isValid);
        $this->assertEmpty($form->getErrors('author'));
        $this->assertEmpty($form->getErrors('email'));
        $this->assertEmpty($form->getErrors('message'));
    }

    /**
     * Test author field is required
     */
    public function testAuthorFieldIsRequired()
    {
        $form = new PostForm();
        $form->email = 'test@example.com';
        $form->message = 'Test message';
        $form->verifyCode = 'testcode';

        $form->validate(['author']);

        $this->assertFalse($form->validate(['author']));
        $this->assertNotEmpty($form->getErrors('author'));
    }

    /**
     * Test email field is required
     */
    public function testEmailFieldIsRequired()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->message = 'Test message';
        $form->verifyCode = 'testcode';

        $this->assertFalse($form->validate(['email']));
        $this->assertNotEmpty($form->getErrors('email'));
    }

    /**
     * Test message field is required
     */
    public function testMessageFieldIsRequired()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->verifyCode = 'testcode';

        $this->assertFalse($form->validate(['message']));
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test author minimum length (2 characters)
     */
    public function testAuthorMinimumLength()
    {
        $form = new PostForm();
        $form->author = 'A'; // Only 1 character
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $this->assertFalse($form->validate(['author']));
        $this->assertNotEmpty($form->getErrors('author'));
        $this->assertStringContainsString('минимум', $form->getFirstError('author'));
    }

    /**
     * Test author maximum length (15 characters)
     */
    public function testAuthorMaximumLength()
    {
        $form = new PostForm();
        $form->author = 'This is a very long name exceeding limit'; // More than 15 characters
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $this->assertFalse($form->validate(['author']));
        $this->assertNotEmpty($form->getErrors('author'));
        $this->assertStringContainsString('превышать', $form->getFirstError('author'));
    }

    /**
     * Test author cannot be only spaces
     */
    public function testAuthorCannotBeOnlySpaces()
    {
        $form = new PostForm();
        $form->author = '   '; // Only spaces
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $this->assertFalse($form->validate(['author']));
        $this->assertNotEmpty($form->getErrors('author'));
    }

    /**
     * Test author with valid edge cases (exactly 2 characters)
     */
    public function testAuthorMinimumLengthValid()
    {
        $form = new PostForm();
        $form->author = 'AB'; // Exactly 2 characters
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $this->assertTrue($form->validate(['author', 'email', 'message']));
        $this->assertEmpty($form->getErrors('author'));
    }

    /**
     * Test author with valid edge cases (exactly 15 characters)
     */
    public function testAuthorMaximumLengthValid()
    {
        $form = new PostForm();
        $form->author = 'Exactly15Chars!'; // Exactly 15 characters
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $this->assertTrue($form->validate(['author', 'email', 'message']));
        $this->assertEmpty($form->getErrors('author'));
    }

    /**
     * Test email format validation (valid emails)
     */
    public function testEmailFormatValid()
    {
        $validEmails = [
            'user@example.com',
            'test.user@example.com',
            'user+tag@example.co.uk',
            'user_name@example.org',
        ];

        foreach ($validEmails as $email) {
            $form = new PostForm();
            $form->author = 'Test User';
            $form->email = $email;
            $form->message = 'Test message';

            $this->assertTrue($form->validate(['email']), "Email '{$email}' should be valid");
            $this->assertEmpty($form->getErrors('email'));
        }
    }

    /**
     * Test email format validation (invalid emails)
     */
    public function testEmailFormatInvalid()
    {
        $invalidEmails = [
            'notanemail',
            '@example.com',
            'user@',
            'user @example.com',
            'user@.com',
        ];

        foreach ($invalidEmails as $email) {
            $form = new PostForm();
            $form->author = 'Test User';
            $form->email = $email;
            $form->message = 'Test message';

            $this->assertFalse($form->validate(['email']), "Email '{$email}' should be invalid");
            $this->assertNotEmpty($form->getErrors('email'));
            $this->assertStringContainsString('Некорректный', $form->getFirstError('email'));
        }
    }

    /**
     * Test email maximum length (255 characters)
     */
    public function testEmailMaximumLength()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        // Create an email longer than 255 characters
        $form->email = str_repeat('a', 250) . '@example.com'; // More than 255 chars
        $form->message = 'Test message';

        $this->assertFalse($form->validate(['email']));
        $this->assertNotEmpty($form->getErrors('email'));
    }

    /**
     * Test message minimum length (5 characters)
     */
    public function testMessageMinimumLength()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = 'Hi'; // Only 2 characters

        $this->assertFalse($form->validate(['message']));
        $this->assertNotEmpty($form->getErrors('message'));
        $this->assertStringContainsString('минимум', $form->getFirstError('message'));
    }

    /**
     * Test message maximum length (1000 characters)
     */
    public function testMessageMaximumLength()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = str_repeat('a', 1001); // 1001 characters

        $this->assertFalse($form->validate(['message']));
        $this->assertNotEmpty($form->getErrors('message'));
        $this->assertStringContainsString('превышать', $form->getFirstError('message'));
    }

    /**
     * Test message cannot be only spaces
     */
    public function testMessageCannotBeOnlySpaces()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = '     '; // Only spaces

        $this->assertFalse($form->validate(['message']));
        $this->assertNotEmpty($form->getErrors('message'));
        $this->assertStringContainsString('пробелов', $form->getFirstError('message'));
    }

    /**
     * Test message with valid edge cases (exactly 5 characters)
     */
    public function testMessageMinimumLengthValid()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = 'Hello'; // Exactly 5 characters

        $this->assertTrue($form->validate(['author', 'email', 'message']));
        $this->assertEmpty($form->getErrors('message'));
    }

    /**
     * Test message with valid edge cases (exactly 1000 characters)
     */
    public function testMessageMaximumLengthValid()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = str_repeat('a', 1000); // Exactly 1000 characters

        $this->assertTrue($form->validate(['author', 'email', 'message']));
        $this->assertEmpty($form->getErrors('message'));
    }

    /**
     * Test whitespace trimming for author
     */
    public function testAuthorWhitespaceTrimming()
    {
        $form = new PostForm();
        $form->author = '  Test User  '; // Spaces before and after
        $form->email = 'test@example.com';
        $form->message = 'Test message';

        $form->validate(['author', 'email', 'message']);

        $this->assertEquals('Test User', $form->author);
    }

    /**
     * Test whitespace trimming for email
     */
    public function testEmailWhitespaceTrimming()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = '  test@example.com  '; // Spaces before and after
        $form->message = 'Test message';

        $form->validate(['author', 'email', 'message']);

        $this->assertEquals('test@example.com', $form->email);
    }

    /**
     * Test whitespace trimming for message
     */
    public function testMessageWhitespaceTrimming()
    {
        $form = new PostForm();
        $form->author = 'Test User';
        $form->email = 'test@example.com';
        $form->message = '  Test message  '; // Spaces before and after

        $form->validate(['author', 'email', 'message']);

        $this->assertEquals('Test message', $form->message);
    }

    /**
     * Test attribute labels are in Russian
     */
    public function testAttributeLabelsAreInRussian()
    {
        $form = new PostForm();
        $labels = $form->attributeLabels();

        $this->assertEquals('Имя автора', $labels['author']);
        $this->assertEquals('Email', $labels['email']);
        $this->assertEquals('Сообщение', $labels['message']);
        $this->assertEquals('Код с картинки', $labels['verifyCode']);
    }
}
