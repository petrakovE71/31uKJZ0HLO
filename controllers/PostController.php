<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PostForm;
use app\models\PostEditForm;
use app\services\PostManagementService;

/**
 * PostController - Thin controller for HTTP handling only
 *
 * Responsibilities:
 * - Create and validate form models
 * - Call business logic services
 * - Set flash messages
 * - Render views or redirect
 *
 * Does NOT contain business logic - delegates to PostManagementService
 */
class PostController extends Controller
{
    /**
     * @var PostManagementService Injected via Yii2 DI
     */
    public $postManagementService;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'confirm-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display posts list and creation form
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new PostForm();

        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $result = $this->postManagementService->createPost($model);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->refresh();
            } else {
                $model->addError('message', $result['message']);
            }
        }

        // Get posts for display
        $postsData = $this->postManagementService->getPostsList(20);

        return $this->render('index', [
            'model' => $model,
            'posts' => $postsData['posts'],
            'pagination' => $postsData['pagination'],
            'totalCount' => $postsData['totalCount'],
        ]);
    }

    /**
     * Edit post by token
     *
     * @param string $token Edit token
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($token)
    {
        // Get post with permission check
        $post = $this->postManagementService->getPostForEdit($token);

        if ($post === null) {
            Yii::$app->session->setFlash('error',
                'Пост не найден, удален или время для редактирования истекло'
            );
            return $this->redirect(['index']);
        }

        // Create and populate form
        $model = new PostEditForm();
        $model->message = $post->message;

        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $result = $this->postManagementService->updatePostByToken($token, $model);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->redirect(['index']);
            } else {
                $model->addError('message', $result['message']);
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'post' => $post,
        ]);
    }

    /**
     * Delete confirmation page
     *
     * @param string $token Delete token
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($token)
    {
        // Get post with permission check
        $post = $this->postManagementService->getPostForDelete($token);

        if ($post === null) {
            Yii::$app->session->setFlash('error',
                'Пост не найден, удален или время для удаления истекло'
            );
            return $this->redirect(['index']);
        }

        return $this->render('delete', [
            'post' => $post,
        ]);
    }

    /**
     * Confirm and execute post deletion
     *
     * @param string $token Delete token
     * @return \yii\web\Response
     */
    public function actionConfirmDelete($token)
    {
        $result = $this->postManagementService->deletePostByToken($token);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['index']);
    }
}
