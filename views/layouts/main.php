<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => 'StoryVault - Оставьте свое сообщение']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

// Custom styles
$this->registerCss("
    body {
        background-color: #f5f5f5;
    }
    .site-header {
        background-color: #343a40;
        padding: 15px 0;
        margin-bottom: 30px;
    }
    .site-header h1 {
        color: #fff;
        margin: 0;
        font-size: 24px;
        font-weight: normal;
    }
");
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Dark Header -->
<header class="site-header">
    <div class="container">
        <h1><?= Html::a('StoryVault', ['/post/index'], ['style' => 'color: #fff; text-decoration: none;']) ?></h1>
    </div>
</header>

<!-- Main Content -->
<main role="main">
    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
