<?php 

Yii::app()->clientScript->registerScript('bansearch', '
	$("#Bans_server_ip").change(function(){
		$.post("'.Yii::app()->createUrl('/bans/index').'", {"server": $(this).val()}, function(data) {eval(data);});
		return false;
	});
');

$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array( 
    'action'=>Yii::app()->createUrl($this->route), 
    'method'=>'get',
    'type'=>'inline'
));

echo $form->textFieldRow($model,'name',array('maxlength'=>100));
echo $form->textFieldRow($model,'steamid',array('maxlength'=>20));

$this->widget('bootstrap.widgets.TbButton', array(
        'buttonType'=>'submit',
        'type'=>'primary',
        'label'=>'Искать'
    ));

$this->endWidget();

?>