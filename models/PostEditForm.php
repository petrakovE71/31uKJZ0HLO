<?php

namespace app\models;

use yii\base\Model;

/**
 * PostEditForm is the model for editing existing posts.
 *
 * @property string $message Post message (5-1000 characters)
 */
class PostEditForm extends Model
{
    public $message;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Required field
            ['message', 'required'],

            // Trim whitespace
            ['message', 'trim'],

            // Message validation
            ['message', 'string', 'min' => 5, 'max' => 1000],
            ['message', 'match', 'pattern' => '/\S/', 'message' => 'Сообщение не может состоять только из пробелов'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'message' => 'Сообщение',
        ];
    }
}
