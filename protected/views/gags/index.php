<?php

$page = 'GAG-лист';
$this->pageTitle = Yii::app()->name . ' - ' . $page;

$this->breadcrumbs=array(
	$page,
);

Yii::app()->clientScript->registerScript('search', "
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('gags-grid', {
        data: $(this).serialize()
    });
    return false;
});
");

$this->renderPartial('_search',array(
    'model'=>$model,
));

$this->widget('bootstrap.widgets.TbGridView', array(
    'type'=>'striped bordered condensed',
	'id'=>'gags-grid',
    'dataProvider'=>isset($_GET['Gags']) ? $model->search() : $dataProvider,
    'enableSorting' => array('create_time', 'name', 'admin_name', 'reason'),
	'summaryText' => 'Показано с {start} по {end} гагов из {count}. Страница {page} из {pages}',
	'htmlOptions' => array(
		'style' => 'width: 100%'
	),
	'rowHtmlOptionsExpression'=>'array(
		"id" => "gag_$data->id",
		"class" => ($data->expired_time < time() && $data->expired_time) ? "bantr success" : "bantr"
	)',
	'pager' => array(
		'class'=>'bootstrap.widgets.TbPager',
		'displayFirstAndLast' => true,
	),
    'columns'=>array(
        array(
            'header' => 'Дата',
            'name' => 'create_time',
            'value' => 'date("d.m.Y H:i", $data->create_time)',
            'htmlOptions' => array('style' => 'width:100px'),
        ),
		array(
			'header' => 'Ник',
			'type' => 'raw',
			'name' => 'name',
			'value' => '$data->country . " " . CHtml::encode($data->name)'
		),

        array(
            'header' => 'STEAM_ID',
            'type' => 'raw',
            'value' => '$data->steamid',
            'htmlOptions' => array(
                'style' => 'width: 130px'
            )
        ),

        array(
            'header' => 'Админ',
            'type' => 'raw',
            'name' => 'admin_name',
            'value' => '$data->admin ? CHtml::link(CHtml::encode(mb_substr($data->admin_name, 0, 18, "UTF-8")), Yii::app()->urlManager->baseUrl . "/amxadmins/#admin_" . $data->admin->id) : CHtml::encode(mb_substr($data->admin_name, 0, 18, "UTF-8"))',
            'htmlOptions' => array(
                'style' => 'width: 130px'
            )
        ),

	array(
		'header' => 'Срок до',
		'value' => '($data->expired_time >= 0) ? ($data->expired_time ? date("d.m.Y H:i", $data->expired_time) : "Навсегда") : "Разбанен"',
		'htmlOptions' => array('style' => 'width:100px'),
	),
	
	array(
		'header' => 'Причина',
		'name' => 'reason',
		'value' => '$data->reason ? $data->reason : ""',
		'htmlOptions' => array('style' => 'width:100px'),
	),

        array(
            'class'=>'bootstrap.widgets.TbButtonColumn',
            'template' => '{delete}',
            'htmlOptions' => array('style' => 'width:20px'),
            'visible' => Webadmins::checkAccess('bans_edit')
		)
	),
));
?>