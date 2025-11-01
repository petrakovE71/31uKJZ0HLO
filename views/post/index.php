<?php

/** @var yii\web\View $this */
/** @var app\models\PostForm $model */
/** @var app\models\Post[] $posts */
/** @var yii\data\Pagination $pagination */
/** @var int $totalCount */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\captcha\Captcha;
use app\helpers\TimeHelper;
use app\helpers\PluralHelper;
use app\services\IpService;
use app\services\HtmlSanitizerService;

$this->title = 'StoryVault';

$ipService = new IpService();
$sanitizer = new HtmlSanitizerService();
?>

<div class="post-index">
    <div class="row">
        <!-- Left Column: Posts List -->
        <div class="col-md-7">
            <?php if ($totalCount > 0): ?>
                <p class="text-muted mb-3">
                    Показаны записи <?= $pagination->offset + 1 ?>-<?= min($pagination->offset + $pagination->limit, $totalCount) ?> из <?= $totalCount ?>.
                </p>
            <?php else: ?>
                <p class="text-muted mb-3">Пока нет сообщений. Будьте первым!</p>
            <?php endif; ?>

            <!-- Posts -->
            <?php foreach ($posts as $post): ?>
                <div class="card card-default mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= Html::encode($post->author->name) ?></h5>
                        <p><?= $sanitizer->sanitize($post->message) ?></p>
                        <p>
                            <small class="text-muted">
                                <?= TimeHelper::relativeFormat($post->created_at) ?> |
                                <?= $ipService->maskIp($post->author->ip_address) ?> |
                                <?= PluralHelper::formatPostsCount($post->author->postsCountByIp) ?>
                            </small>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalCount > 0): ?>
                <div class="mt-4">
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                        'options' => ['class' => 'pagination'],
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Create Post Form -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'post-form',
                        'options' => ['class' => 'form'],
                        'enableClientValidation' => true,
                        'enableAjaxValidation' => false,
                        'validateOnSubmit' => true,
                        'validateOnChange' => true,
                        'validateOnBlur' => true,
                    ]); ?>

                    <?= $form->field($model, 'author')->textInput([
                        'maxlength' => 15,
                        'minlength' => 2,
                        'placeholder' => 'Андрей'
                    ])->label('Имя автора') ?>

                    <?= $form->field($model, 'email')->textInput([
                        'maxlength' => 255,
                        'type' => 'email',
                        'placeholder' => 'yourmail.com'
                    ])->label('Email') ?>

                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 6,
                        'maxlength' => 1000,
                        'minlength' => 5,
                        'placeholder' => 'Оставьте свой текстовый след в истории'
                    ])->label('Сообщение') ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="mb-2"><strong>Код с картинки:</strong></div><div class="row"><div class="col-lg-6 mb-2">{image}</div><div class="col-lg-6">{input}</div></div>',
                        'captchaAction' => 'site/captcha',
                        'imageOptions' => ['alt' => 'Код подтверждения', 'style' => 'cursor: pointer;'],
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите код']
                    ])->label(false) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Отправить', ['class' => 'btn btn-success w-100']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
