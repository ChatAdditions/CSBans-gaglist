<?php

class GagsController extends Controller
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
		//$model=new Gags('search');
        $model = Gags::model()->with('admin');
		$model->unsetAttributes();
		if (isset($_GET['Gags'])) {
            $model->attributes = $_GET['Gags'];
        }

		$dataProvider=new CActiveDataProvider('Gags', array(
			'criteria' => array(
                'order' => '`create_time` DESC'),
			'pagination' => array(
				'pageSize' =>  Yii::app()->config->bans_per_page)
            )
		 );

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'model'=>$model
		));

	}

	public function loadModel($id)
	{
		$model=Gags::model()->with('admin')->findByPk($id);
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
