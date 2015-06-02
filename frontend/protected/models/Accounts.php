<?php

/**
 * This is the model class for table "skeez_accounts".
 *
 * The followings are the available columns in table 'skeez_accounts':
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $avatar
 * @property string $gender
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string $address
 * @property string $postcode
 * @property string $city
 * @property string $country
 * @property integer $active
 * @property string $confirm_token
 * @property string $forgot_token
 * @property string $created
 * @property string $modified
 */
class Accounts extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return Yii::app()->params['db_prefix'] . 'accounts';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('username, password, first_name, last_name, email, phone, address, postcode, city, country', 'required'),
            array('username, email', 'unique'),
            array('active', 'numerical', 'integerOnly'=>true),
            array('username, confirm_token, forgot_token', 'length', 'max'=>50),
            array('password', 'length', 'max'=>128),
            array('avatar', 'length', 'max'=>50),
            array('gender', 'length', 'max'=>6),
            array('first_name, last_name, email, phone, city, country', 'length', 'max'=>45),
            array('address', 'length', 'max'=>255),
            array('postcode', 'length', 'max'=>10),
            array('created, modified', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, username, password, email, avatar, gender, first_name, last_name, phone, address, postcode, city, country, active, confirm_token, forgot_token, created, modified', 'safe', 'on'=>'search'),
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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'email' => 'Email',
            'avatar' => 'Avatar',
            'gender' => 'Gender',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'postcode' => 'Postcode',
            'city' => 'City',
            'country' => 'Country',
            'active' => 'Account Table',
            'confirm_token' => 'Confirm Token',
            'forgot_token' => 'Forgot Token',
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
        $criteria->compare('username',$this->username,true);
        $criteria->compare('password',$this->password,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('avatar',$this->avatar,true);
        $criteria->compare('gender',$this->gender,true);
        $criteria->compare('first_name',$this->first_name,true);
        $criteria->compare('last_name',$this->last_name,true);
        $criteria->compare('phone',$this->phone,true);
        $criteria->compare('address',$this->address,true);
        $criteria->compare('postcode',$this->postcode,true);
        $criteria->compare('city',$this->city,true);
        $criteria->compare('country',$this->country,true);
        $criteria->compare('active',$this->active);
        $criteria->compare('confirm_token',$this->confirm_token,true);
        $criteria->compare('forgot_token',$this->forgot_token,true);
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
     * @return Accounts the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
