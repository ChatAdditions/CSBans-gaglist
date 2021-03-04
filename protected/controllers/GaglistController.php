<?php

class GaglistController extends Controller
{
    public $layout='//layouts/column1';

    public function filters()
    {
        return array(
            'accessControl',
            'postOnly + delete'
        );
    }

    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction'
            )
        );
    }

    public function actionDelete($id)
    {
        $model = $this->loadModel($id);

        if (!Webadmins::checkAccess('bans_delete')) {
            throw new CHttpException(403, "У Вас недостаточно прав");
        }

        $model->delete();

        if (!isset($_GET['ajax'])) {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
    }

    public function actionIndex()
    {
        //$model=new Gaglist('search');
        $model = Gaglist::model()->with('admin');
        $model->unsetAttributes();
        if (isset($_GET['Gaglist'])) {
            $model->attributes = $_GET['Gaglist'];
        }

        $dataProvider=new CActiveDataProvider('Gaglist', array(
            'pagination' => array(
                'pageSize' =>  Yii::app()->config->bans_per_page),
                'sort' => array(
                    'defaultOrder' => '`created_at` DESC',
                    'attributes' => array(
                        'created_at',
                        'name',
                        'admin_name',
                        'reason'
                    )
                )
            )
         );

        $this->render('index',array(
            'dataProvider'=>$dataProvider,
            'model'=>$model
        ));

    }

    public function loadModel($id)
    {
        $model=Gaglist::model()->with('admin')->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'Запись не найдена.');
        }
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='gags-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
