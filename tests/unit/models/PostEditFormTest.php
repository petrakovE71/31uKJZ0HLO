<?php

namespace tests\unit\models;

use app\models\PostEditForm;
use Codeception\Test\Unit;

class PostEditFormTest extends Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    /**
     * Test validation passes with valid message
     */
    public function testValidationPassesWithValidMessage()
    {
        $form = new PostEditForm();
        $form->message = 'This is a valid updated message';

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test message field is required
     */
    public function testMessageFieldIsRequired()
    {
        $form = new PostEditForm();
        // Don't set message

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message with empty string fails validation
     */
    public function testMessageEmptyStringFailsValidation()
    {
        $form = new PostEditForm();
        $form->message = '';

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message minimum length (5 characters)
     */
    public function testMessageMinimumLength()
    {
        $form = new PostEditForm();
        $form->message = 'Hi'; // Only 2 characters

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message maximum length (1000 characters)
     */
    public function testMessageMaximumLength()
    {
        $form = new PostEditForm();
        $form->message = str_repeat('a', 1001); // 1001 characters

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message cannot be only spaces
     */
    public function testMessageCannotBeOnlySpaces()
    {
        $form = new PostEditForm();
        $form->message = '     '; // Only spaces

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
        $this->assertStringContainsString('пробелов', $form->getFirstError('message'));
    }

    /**
     * Test message with valid edge case (exactly 5 characters)
     */
    public function testMessageMinimumLengthValid()
    {
        $form = new PostEditForm();
        $form->message = 'Hello'; // Exactly 5 characters

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test message with valid edge case (exactly 1000 characters)
     */
    public function testMessageMaximumLengthValid()
    {
        $form = new PostEditForm();
        $form->message = str_repeat('a', 1000); // Exactly 1000 characters

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test whitespace trimming
     */
    public function testMessageWhitespaceTrimming()
    {
        $form = new PostEditForm();
        $form->message = '  Test message with spaces  '; // Spaces before and after

        $form->validate();

        $this->assertEquals('Test message with spaces', $form->message);
    }

    /**
     * Test message with leading spaces gets trimmed before validation
     */
    public function testMessageLeadingSpacesTrimmed()
    {
        $form = new PostEditForm();
        $form->message = '     Valid message after spaces';

        $this->assertTrue($form->validate());
        $this->assertEquals('Valid message after spaces', $form->message);
    }

    /**
     * Test message with trailing spaces gets trimmed before validation
     */
    public function testMessageTrailingSpacesTrimmed()
    {
        $form = new PostEditForm();
        $form->message = 'Valid message before spaces     ';

        $this->assertTrue($form->validate());
        $this->assertEquals('Valid message before spaces', $form->message);
    }

    /**
     * Test message with newlines is valid (as long as it has content)
     */
    public function testMessageWithNewlinesIsValid()
    {
        $form = new PostEditForm();
        $form->message = "Line 1\nLine 2\nLine 3";

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test message with special characters is valid
     */
    public function testMessageWithSpecialCharactersIsValid()
    {
        $form = new PostEditForm();
        $form->message = 'Message with special chars: @#$%^&*()';

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test message with Unicode/Cyrillic characters
     */
    public function testMessageWithCyrillicCharactersIsValid()
    {
        $form = new PostEditForm();
        $form->message = 'Тестовое сообщение на русском языке';

        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }

    /**
     * Test message with mixed content (spaces, text, newlines)
     */
    public function testMessageWithMixedContentIsValid()
    {
        $form = new PostEditForm();
        $form->message = "  First paragraph\n\n  Second paragraph  ";

        $this->assertTrue($form->validate());
        // After trim, should be: "First paragraph\n\n  Second paragraph"
        $this->assertStringContainsString('First paragraph', $form->message);
    }

    /**
     * Test attribute label is in Russian
     */
    public function testAttributeLabelIsInRussian()
    {
        $form = new PostEditForm();
        $labels = $form->attributeLabels();

        $this->assertEquals('Сообщение', $labels['message']);
    }

    /**
     * Test message with only newlines and spaces fails
     */
    public function testMessageWithOnlyNewlinesAndSpacesFails()
    {
        $form = new PostEditForm();
        $form->message = "\n\n   \n   \n";

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message with valid length after trimming
     */
    public function testMessageValidLengthAfterTrimming()
    {
        $form = new PostEditForm();
        // After trimming, this will be exactly 5 characters "Valid"
        $form->message = '     Valid     ';

        $this->assertTrue($form->validate());
        $this->assertEquals('Valid', $form->message);
    }

    /**
     * Test message too short after trimming
     */
    public function testMessageTooShortAfterTrimming()
    {
        $form = new PostEditForm();
        // After trimming, this will be only 2 characters "Hi"
        $form->message = '     Hi     ';

        $this->assertFalse($form->validate());
        $this->assertNotEmpty($form->getErrors('message'));
    }

    /**
     * Test message with HTML tags (should be allowed, sanitization happens elsewhere)
     */
    public function testMessageWithHtmlTagsIsValid()
    {
        $form = new PostEditForm();
        $form->message = '<p>This is a message with HTML</p>';

        // Form should allow HTML, sanitization happens in the view layer
        $this->assertTrue($form->validate());
        $this->assertEmpty($form->getErrors());
    }
}
