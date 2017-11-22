<?php
/**
 * @property integer $id ID бана
 * @property string $steamid Стим игрока
 * @property string $name Ник игрока
 * @property string $ip IP игрока
 * @property integer $unban_time Дата истечения бана
 *
 * The followings are the available model relations:
 * @property Amxadmins $admin
 */
class Gags extends CActiveRecord
{
	public $country = null;

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function tableName()
	{
		return 'ucc_gag';
	}

    public function rules()
	{
		return array(
			array('name', 'required'),
			array('ip', 'match', 'pattern' => '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/'),
			array('steamid', 'match', 'pattern' => '/^(STEAM|VALVE)_([0-9]):([0-9]):\d{1,21}$/'),
			array('id, steamid, name, ip, unban_time', 'safe', 'on'=>'search'),
		);
	}

    public function relations()
	{
		return array(
            'admin' => array(
                self::HAS_ONE,
                'Amxadmins',
                '',
                'on' => '`admin`.`steamid` = `t`.`admin_nick` OR '
                    . '`admin`.`steamid` = `t`.`admin_ip` OR '
                    . '`admin`.`steamid` = `t`.`admin_id`'
            )
		);
	}

	public function attributeLabels()
	{
		return array(
			'id'				=> 'Bid',
			'ip'		    	=> 'IP игрока',
			'steamid'			=> 'Steam  игрока',
			'name'		        => 'Ник игрока',
			'unban_time'		=> 'Истекает',
			'city'				=> 'Город'
		);
	}

    protected function afterFind() {
		$country = strtolower(Yii::app()->IpToCountry->lookup($this->ip));
		$this->country = CHtml::image(
            Yii::app()->urlManager->baseUrl 
            . '/images/country/' 
            . ($country != 'zz' ? $country : 'clear') . '.png'
        );
        return parent::afterFind();
	}

    public function afterDelete() {
		Syslog::add(Logs::LOG_DELETED, 'Удален gag игрока <strong>' . $this->name . '</strong>');
		return parent::afterDelete();
	}

    public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->addSearchCondition('ip',$this->ip);
		$criteria->addSearchCondition('steamid',$this->steamid);
		$criteria->addSearchCondition('name',$this->name);

		$criteria->order = '`id` DESC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination' => array(
				'pageSize' => Yii::app()->config->bans_per_page
			)
		));
	}
}
