<?php
/**
 * @property integer $id ID бана
 * @property string $steamid Стим игрока
 * @property string $name Ник игрока
 * @property string $ip IP игрока
 * @property string $admin_name Ник админа
 * @property string $admin_steamid Стим админа
 * @property integer $create_time Дата бана
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
			array('name, admin_name', 'required'),
			array('ip', 'match', 'pattern' => '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/'),
			array('steamid, admin_steamid', 'match', 'pattern' => '/^(STEAM|VALVE)_([0-9]):([0-9]):\d{1,21}$/'),
			array('id, steamid, name, ip, admin_name, admin_steamid, create_time, unban_time', 'safe', 'on'=>'search'),
		);
	}

    public function relations()
	{
		return array(
            'admin' => array(
                self::HAS_ONE,
                'Amxadmins',
                '',
                'on' => '`admin`.`steamid` = `t`.`admin_name` OR '
                    . '`admin`.`steamid` = `t`.`admin_steamid`'
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
			'admin_name'		=> 'Ник админа'
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

		$criteria->compare('t.id',$this->id);
		$criteria->addSearchCondition('t.ip',$this->ip);
		$criteria->addSearchCondition('t.steamid',$this->steamid);
		$criteria->addSearchCondition('t.name',$this->name);
        $criteria->addSearchCondition('t.admin_name',$this->admin_name);

		$criteria->order = '`create_time` DESC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination' => array(
				'pageSize' => Yii::app()->config->bans_per_page
			)
		));
	}
}
