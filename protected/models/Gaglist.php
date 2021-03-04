<?php
/**
 * @property integer $id ID бана
 * @property string $authid Стим игрока
 * @property string $name Ник игрока
 * @property string $ip IP игрока
 * @property string $admin_name Ник админа
 * @property string $admin_authid Стим админа
 * @property integer $created_at Дата бана
 * @property integer $expire_at Дата истечения бана
 * @property integer $reason Причина
 *
 * The followings are the available model relations:
 * @property Amxadmins $admin
 */
class Gaglist extends CActiveRecord
{
    public $country = null;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'players_gags';
    }

    public function rules()
    {
        return array(
            array('name, admin_name', 'required'),
            array('ip', 'match', 'pattern' => '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/'),
            array('authid, admin_authid', 'match', 'pattern' => '/^(STEAM|VALVE)_([0-9]):([0-9]):\d{1,21}$/'),
            array('id, authid, name, ip, admin_name, admin_authid, created_at, expire_at', 'safe', 'on'=>'search'),
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
                    . '`admin`.`steamid` = `t`.`admin_authid`'
            )
        );
    }

    public function attributeLabels()
    {
        return array(
            'id'                => 'Bid',
            'ip'                => 'IP игрока',
            'authid'            => 'Steam  игрока',
            'name'              => 'Ник игрока',
            'expire_at'         => 'Истекает',
            'admin_name'        => 'Ник админа',
            'reason'            => 'Причина'
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
        $criteria->addSearchCondition('t.authid',$this->authid);
        $criteria->addSearchCondition('t.name',$this->name);
        $criteria->addSearchCondition('t.admin_name',$this->admin_name);

        $criteria->order = '`created_at` DESC';

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'pagination' => array(
                'pageSize' => Yii::app()->config->bans_per_page
            )
        ));
    }
}
