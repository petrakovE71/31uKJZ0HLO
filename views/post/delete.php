<?php

/** @var yii\web\View $this */
/** @var app\models\Post $post */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Удаление поста';
?>

<div class="post-delete">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><?= Html::encode($this->title) ?></h2>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5>⚠️ Внимание!</h5>
                        <p>Вы действительно хотите удалить это сообщение?</p>
                        <p class="mb-0">Это действие нельзя отменить.</p>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= Html::encode($post->author->name) ?></h5>
                            <p class="card-text"><?= Html::encode($post->message) ?></p>
                            <p class="text-muted small mb-0">
                                <strong>Опубликовано:</strong> <?= date('d.m.Y H:i', $post->created_at) ?>
                            </p>
                        </div>
                    </div>

                    <p class="alert alert-info">
                        Удаление доступно в течение 14 дней после публикации.
                    </p>

                    <div class="mt-3">
                        <?php $form = ActiveForm::begin([
                            'action' => ['confirm-delete', 'token' => $post->delete_token],
                            'method' => 'post',
                        ]); ?>

                        <?= Html::submitButton('Да, удалить пост', [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить этот пост?',
                            ],
                        ]) ?>

                        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
