<?php
use yii\helpers\Html;

/** @var app\models\Author $author */
/** @var app\models\Post $post */
/** @var string $editUrl */
/** @var string $deleteUrl */
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ваше сообщение опубликовано</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #343a40;">Здравствуйте, <?= Html::encode($author->name) ?>!</h2>

        <p>Ваше сообщение успешно опубликовано на <strong>StoryVault</strong>.</p>

        <div style="background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><em><?= Html::encode($post->message) ?></em></p>
        </div>

        <h3 style="color: #343a40;">Управление постом:</h3>

        <p>
            <strong>Редактировать</strong> (доступно 12 часов):<br>
            <a href="<?= $editUrl ?>" style="color: #007bff; text-decoration: none;"><?= $editUrl ?></a>
        </p>

        <p>
            <strong>Удалить</strong> (доступно 14 дней):<br>
            <a href="<?= $deleteUrl ?>" style="color: #dc3545; text-decoration: none;"><?= $deleteUrl ?></a>
        </p>

        <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">

        <p style="color: #6c757d; font-size: 12px;">
            Это письмо было отправлено автоматически. Пожалуйста, не отвечайте на него.<br>
            <strong>StoryVault</strong> - Оставьте свой след в истории
        </p>
    </div>
</body>
</html>
