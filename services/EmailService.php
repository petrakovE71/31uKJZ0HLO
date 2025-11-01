<?php

namespace app\services;

use Yii;
use app\models\Post;
use app\models\Author;
use app\exceptions\EmailNotificationException;
use yii\helpers\Url;

/**
 * EmailService handles sending emails with validation and error handling
 */
class EmailService
{
    /**
     * Send post created email to author
     *
     * Validates email configuration and handles sending errors
     *
     * @param Post $post
     * @param Author $author
     * @return bool
     * @throws EmailNotificationException
     */
    public function sendPostCreatedEmail(Post $post, Author $author)
    {
        // Validate input
        if (!$post || !$author) {
            throw new EmailNotificationException(
                'Invalid post or author object',
                'Не удалось отправить email: неверные данные'
            );
        }

        // Validate email address
        if (!$this->validateEmail($author->email)) {
            throw new EmailNotificationException(
                "Invalid email address: {$author->email}",
                'Не удалось отправить email: неверный адрес'
            );
        }

        // Validate email configuration
        if (!$this->validateEmailConfig()) {
            throw new EmailNotificationException(
                'Email configuration missing: senderEmail or senderName',
                'Не удалось отправить email: ошибка конфигурации'
            );
        }

        try {
            // Generate URLs
            $editUrl = Url::to(['post/edit', 'token' => $post->edit_token], true);
            $deleteUrl = Url::to(['post/delete', 'token' => $post->delete_token], true);

            // Send email
            $result = Yii::$app->mailer->compose(
                ['html' => 'post-created-html', 'text' => 'post-created-text'],
                [
                    'author' => $author,
                    'post' => $post,
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,
                ]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($author->email)
            ->setSubject('Ваше сообщение опубликовано на StoryVault')
            ->send();

            if (!$result) {
                throw new EmailNotificationException(
                    "Failed to send email to {$author->email}",
                    'Не удалось отправить email'
                );
            }

            Yii::info("Email sent successfully to {$author->email}", __METHOD__);
            return true;

        } catch (EmailNotificationException $e) {
            // Re-throw our custom exception
            throw $e;

        } catch (\Exception $e) {
            // Wrap unexpected errors in EmailNotificationException
            throw new EmailNotificationException(
                "Email sending failed: {$e->getMessage()}",
                'Не удалось отправить email: системная ошибка',
                0,
                $e
            );
        }
    }

    /**
     * Validate email address format
     *
     * @param string $email
     * @return bool
     */
    private function validateEmail($email)
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate email configuration parameters
     *
     * @return bool
     */
    private function validateEmailConfig()
    {
        return !empty(Yii::$app->params['senderEmail'])
            && !empty(Yii::$app->params['senderName']);
    }
}
