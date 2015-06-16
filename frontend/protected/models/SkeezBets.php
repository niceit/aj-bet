<?php

/**
 * This is the model class for table "skeez_bets".
 *
 * The followings are the available columns in table 'skeez_bets':
 * @property string $id
 * @property string $account_id
 * @property string $friend_id
 * @property string $match_id
 * @property string $score_1
 * @property string $score_2
 * @property string $approve
 * @property string $created
 * @property string $modified
 *
 * The followings are the available model relations:
 * @property SkeezMatches $match
 */
class SkeezBets extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'skeez_bets';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, friend_id, match_id, score_1, score_2', 'required'),
			array('account_id, friend_id, match_id, score_1, score_2', 'length', 'max'=>10),
			array('approve', 'length', 'max'=>45),
			array('created, modified', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, friend_id, match_id, score_1, score_2, approve, created, modified', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'match' => array(self::BELONGS_TO, 'SkeezMatches', 'match_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'account_id' => 'Account',
			'friend_id' => 'Friend',
			'match_id' => 'Match',
			'score_1' => 'Score 1',
			'score_2' => 'Score 2',
			'approve' => 'Approve',
			'created' => 'Created',
			'modified' => 'Modified',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('account_id',$this->account_id,true);
		$criteria->compare('friend_id',$this->friend_id,true);
		$criteria->compare('match_id',$this->match_id,true);
		$criteria->compare('score_1',$this->score_1,true);
		$criteria->compare('score_2',$this->score_2,true);
		$criteria->compare('approve',$this->approve,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('modified',$this->modified,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkeezBets the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
