<?php

/*
 * 游戏等级模型
 * model()创建一个模型对象
 * tableName()返回当前数据表的名字
 * CActiveRecord 是活跃记录，好多成熟框架都有此技术
 * 元宝
 */

class Gamegrade extends CActiveRecord {
    /*
     * 返回当前模型对象的静态方法
     */

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /*
     * 返回当前模型的名字
     */

    public function tableName() {
        //xm 前缀去掉
        return '{{game_grade}}';
    }

    /*
     * 实现验证
     */

    public function rules() {
        return array(
            array('level', 'required', 'message' => '请填写等级！'),
            array('level', 'match', 'pattern' => '/^[0-9]*$/', 'message' => '等级只能填写数字！'),
        );
    }

    /*
     * 对应标签名称
     */

    function attributeLabels() {
        return array(
            'name' => '等级前缀',
			 'zhi' => '等级后缀',
            'level' => '等级',
            'money' => '厂商奖励金额',
            'hlb' => '、网络奖励元宝',
            'game_id' => '游戏id',
        );
    }

}
