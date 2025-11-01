<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * PostForm is the model behind the post creation form.
 *
 * @property string $author Author name (2-15 characters)
 * @property string $email Author email
 * @property string $message Post message (5-1000 characters)
 * @property string $verifyCode Captcha verification code
 */
class PostForm extends Model
{
    public $author;
    public $email;
    public $message;
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Required fields
            [['author', 'email', 'message', 'verifyCode'], 'required'],

            // Trim whitespace
            [['author', 'email', 'message'], 'trim'],

            // Author validation
            ['author', 'string', 'min' => 2, 'max' => 15, 'tooShort' => 'Имя автора должно содержать минимум {min} символа', 'tooLong' => 'Имя автора не должно превышать {max} символов'],
            ['author', 'match', 'pattern' => '/^\S.*\S$/', 'message' => 'Имя не может состоять только из пробелов'],

            // Email validation
            ['email', 'email', 'message' => 'Некорректный формат email'],
            ['email', 'string', 'max' => 255],

            // Message validation
            ['message', 'string', 'min' => 5, 'max' => 1000, 'tooShort' => 'Сообщение должно содержать минимум {min} символов', 'tooLong' => 'Сообщение не должно превышать {max} символов'],
            ['message', 'match', 'pattern' => '/\S/', 'message' => 'Сообщение не может состоять только из пробелов'],

            // Captcha validation
            ['verifyCode', 'captcha', 'captchaAction' => 'site/captcha'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'author' => 'Имя автора',
            'email' => 'Email',
            'message' => 'Сообщение',
            'verifyCode' => 'Код с картинки',
        ];
    }
}
