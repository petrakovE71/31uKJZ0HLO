<?php

/** @var yii\web\View $this */
/** @var app\models\PostEditForm $model */
/** @var app\models\Post $post */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Редактирование поста';
?>

<div class="post-edit">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><?= Html::encode($this->title) ?></h2>

            <div class="card mt-4">
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Автор:</strong> <?= Html::encode($post->author->name) ?><br>
                        <strong>Email:</strong> <?= Html::encode($post->author->email) ?><br>
                        <strong>Опубликовано:</strong> <?= date('d.m.Y H:i', $post->created_at) ?>
                    </p>

                    <hr>

                    <p class="alert alert-info">
                        Редактирование доступно в течение 12 часов после публикации.
                        Можно изменить только текст сообщения.
                    </p>

                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 8,
                        'maxlength' => 1000,
                    ])->label('Сообщение')
                        ->hint('Разрешены HTML-теги: &lt;b&gt;, &lt;i&gt;, &lt;s&gt;') ?>

                    <div class="form-group mt-3">
                        <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
