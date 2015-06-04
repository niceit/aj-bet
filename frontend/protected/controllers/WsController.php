<?php

class WsController extends Controller
{
    //Json data return back to client
    private $_json_result;

    public function filters(){
        $this->_json_result = array('status' => 0, 'message' => array('Nothing performed'));
    }

	public function actionIndex()
	{
		$this->sendResponse("application/json", $this->_json_result);
	}

    /*
     * User registration action
     * */
    public function actionRegister(){
        if (Yii::app()->request->isPostRequest){
            $data = Yii::app()->request->getPost('Account');
            $Account = new Accounts();
            $Account->attributes = $data;

            //Validate registration data
            if ($Account->validate()){
                if ($data['password'] != $data['retype_password']) {
                    $this->_json_result['message'] = array ('Password confirmation does not match');
                }
                else {
                    $now = date('d-m-Y');

                    //Encrypt password
                    $password_encrypt = md5($data['password'] . $now);
                    $Account->setAttribute('password', $password_encrypt);

                    //Set activation token
                    $activation_token = sha1($data['username'] . $now);
                    $Account->setAttribute('confirm_token', $activation_token);

                    //Set account to active
                    $Account->setAttribute('active', 1);
                    $Account->setAttribute('created', new CDbExpression('NOW()'));
                    $Account->setAttribute('modified', new CDbExpression('NOW()'));

                    if ($Account->save()){
                        $this->_json_result = array('status' => 1, 'message' => array('Account created successfully'));
                        $SkeezBetMailer = new SkeezBetMailer();
                        $account_mail = array(
                            'first_name' => $Account->first_name,
                            'username' => $Account->username,
                            'password' => $data['password']
                        );
                        $sendMail = $SkeezBetMailer->sendWelcomeEmail($Account->email, $account_mail);
                        if (!$sendMail){
                            $this->_json_result = array('status' => 0, 'message' => array('Can not send email'));
                        }
                    }
                    else {
                        $this->_json_result['message'] = $Account->getErrors();
                    }
                }
            }
            else{
                $this->_json_result['message'] = $Account->getErrors();
            }
        }

        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
   * User login action
   * */
    public function actionLogin(){
        if (Yii::app()->request->isPostRequest) {
            $username = Yii::app()->request->getPost('username');
            $password = Yii::app()->request->getPost('password');

            //Check username and password
            if (empty($username)){
                $this->_json_result['message'] = array('Invalid login information');
                $this->sendResponse("application/json", $this->_json_result);
            }

            if (empty($password)){
                $this->_json_result['message'] = array('Invalid login information');
                $this->sendResponse("application/json", $this->_json_result);
            }

            //Check login
            $result = $this->checkLogin($username, $password);
            if (!empty($result)) {
                if($result->getActive() == 1) {
                    $account = array(
                        'id'            => $result->getId(),
                        'first_name'    => $result->getFirstname(),
                        'last_name'     => $result->getLastname()
                    );
                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Logged in successfully');
                    $this->_json_result['account'] = array($account);
                }
                else $this->_json_result['message'] = array('Please active your account before using');
            }
            else $this->_json_result['message'] = array('Invalid login information');
        }

        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
   * User BetCategories action
   * */
    public function actionProfile (){
        if (Yii::app()->request->isPostRequest) {
            $data = Yii::app()->request->getPost('Account');
            $Account = Accounts::model()->findByPk($data['id']);
            $Account->attributes = $data;
            $Account->setAttribute('modified', new CDbExpression('NOW()'));

            if ($Account->save()){

                $this->_json_result = array('status' => 1, 'message' => array('Account update successfully'));
            }
            else {
                $this->_json_result['message'] = $Account->getErrors();
            }
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    * User reset password action
    * */
    public function actionResetPassword (){
        if (Yii::app()->request->isPostRequest) {
            $email = Yii::app()->request->getPost('email');
            $token = Yii::app()->request->getPost('token');
            $password = Yii::app()->request->getPost('password');
            $retype_password = Yii::app()->request->getPost('retype_password');

            if(empty($email)){
                $this->_json_result['message'] = array('Invalid email');
                $this->sendResponse("application/json", $this->_json_result);
            }
            if(empty($token)){
                $this->_json_result['message'] = array('Requested token is invalid');
                $this->sendResponse("application/json", $this->_json_result);
            }
            if (empty($password)){
                $this->_json_result['message'] = array('Please fill your password');
                $this->sendResponse("application/json", $this->_json_result);
            }
            if (empty($retype_password)){
                $this->_json_result['message'] = array('Please confirm your password');
                $this->sendResponse("application/json", $this->_json_result);
            }
            if ($retype_password != $password) {
                $this->_json_result['message'] = array('Password confirmation did not match');
                $this->sendResponse("application/json", $this->_json_result);
            }

            //Fetch account
            $account = Accounts::model()->find ("email = '{$email}' AND confirm_token = '{$token}'");
            if(!empty($account)){
                $date = date_format(date_create($account->created), "d-m-Y");
                $account->setAttribute('password', md5($password . $date));

                if ($account->save()){
                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Password has been changed');
                }
                else $this->_json_result['message'] = $account->getErrors();
            }
            else $this->_json_result['message'] = array('Update failed');
        }

        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    * User Forgot password action
    * */
    public function actionForgot (){
        if (Yii::app()->request->isPostRequest) {
            $email = Yii::app()->request->getPost('email');
            if(empty($email)){
                $this->_json_result['message'] = array('Invalid email information');
                $this->sendResponse("application/json", $this->_json_result);
            }

            $account = Accounts::model()->find( "email = '{$email}'");

            if(!empty($account)) {
                $now = date('d-m-Y');

                //Set forgot token
                $activation_token = sha1($email . $now);
                $account->setAttribute('forgot_token', $activation_token);

                if($account->save()){

                    // send link mail forgot password
                    $link_active = '?token='.$activation_token;
                    $SkeezBetMailer = new SkeezBetMailer();
                    $SkeezBetMailer->addData($account->email, 'Forgot password', 'Body test <a href="'.$link_active.'">'.$link_active.'</a>');

                    if($SkeezBetMailer->SendMail()){
                        $this->_json_result['status'] = 1;
                        $this->_json_result['message'] = array ('Instruction has been sent to your email.');
                    }
                    else $this->_json_result['message'] = array ('Could not send email');
                }
                else $this->_json_result['message'] = $account->getErrors();
            }
            else $this->_json_result['message'] = array('The email address was not found in our records, please try again!');
        }

        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    * User BetCategories action
    * */
    public function actionBetCategories (){
        $Categories = array();
        $categories = SkeezBetCategories::model()->findAll();
        if($categories){
            foreach($categories as $category){
                $Categories[] = array(
                    'id'    => $category->id,
                    'name'  => $category->name
                );
            }
        }

        $this->_json_result['status'] = 1;
        $this->_json_result['categories'] = array($Categories);
        $this->_json_result['message'] = array('Categories successfully loaded');
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    * User BetCategories action
    * */
    public function actionBetSubCategories (){
        if (Yii::app()->request->isPostRequest) {
            $SubCategories = array();
            $parent_category_id = Yii::app()->request->getPost('parent_category_id');
            if(empty($parent_category_id)){
                $this->_json_result['message'] = array('Invalid Categories information');
                $this->sendResponse("application/json", $this->_json_result);
            }

            if (SkeezBetCategories::model()->findByPk($parent_category_id)) {
                $subcategories = SkeezBetSubCategories::model()->findAll('parent_category_id = '.$parent_category_id);
                if($subcategories){
                    foreach($subcategories as $subcategory){
                        $SubCategories[] = array(
                            'id'    => $subcategory->id,
                            'name'  => $subcategory->name
                        );
                    }

                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Categories successfully loaded');
                    $this->_json_result['categories'] = array($SubCategories);
                }
                else{
                    $this->_json_result['message'] = array('There is no sub-category available');
                }
            }
            else $this->_json_result['message'] = array('Parent category was not found');
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
     * Send back request to client
     * */
    private function sendResponse($type, $data, $json_data = true){
        header ("Content-Type: {$type}");
        if ($json_data){
            Yii::app()->end (json_encode($data));
        }
        else Yii::app()->end ($data);
    }

    /*
     * Function check login from model
     * */
    private function checkLogin($username, $password) {

        $identity = new UserIdentity($username, $password);
        $identity->authenticate();
        if ($identity->errorCode === UserIdentity::ERROR_NONE) {
            return $identity;
        } else return null;
    }
}