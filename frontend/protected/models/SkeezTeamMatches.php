<?php

/**
 * This is the model class for table "skeez_team_matches".
 *
 * The followings are the available columns in table 'skeez_team_matches':
 * @property string $id
 * @property string $home
 * @property string $opponent
 * @property string $created
 * @property string $modified
 *
 * The followings are the available model relations:
 * @property SkeezTeams $home0
 * @property SkeezTeams $opponent0
 */
class SkeezTeamMatches extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'skeez_team_matches';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('home, opponent', 'required'),
			array('home, opponent', 'length', 'max'=>10),
			array('created, modified', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, home, opponent, created, modified', 'safe', 'on'=>'search'),
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
			'home0' => array(self::BELONGS_TO, 'SkeezTeams', 'home'),
			'opponent0' => array(self::BELONGS_TO, 'SkeezTeams', 'opponent'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'home' => 'Home',
			'opponent' => 'Opponent',
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
		$criteria->compare('home',$this->home,true);
		$criteria->compare('opponent',$this->opponent,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('modified',$this->modified,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /*
     * Get list teamMatches
     */
    public function getTeamMatches($league_id = 0){
        $db_prefix = Yii::app()->params['db_prefix'];
        $array = Yii::app()->db->createCommand()
            ->select("mt.home , mt.opponent, m.match_time")
            ->from($db_prefix.'team_matches mt')
            ->join($db_prefix.'matches m', 'm.team_match = mt.id')
            ->where('m.league_id = '.$league_id )
            ->andWhere('m.match_time >=  (now() + INTERVAL 5 MINUTE)')
            ->queryAll();
        return $array;
    }

    /*
     * Get list teamMatchesBet
     */
    public function getMatchesTeamMatches($match_id = 0){
        $db_prefix = Yii::app()->params['db_prefix'];
        $array = Yii::app()->db->createCommand()
            ->select("mt.home , mt.opponent, m.id, m.match_time")
            ->from($db_prefix.'matches m')
            ->join($db_prefix.'team_matches mt', 'm.team_match = mt.id')
            ->where('m.id = '.$match_id )
            ->queryRow();
        return $array;
    }
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkeezTeamMatches the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
