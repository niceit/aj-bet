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
    * User Forgot action
    * */
    public function actionForgot (){
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
                else  $this->_json_result['message'] = $account->getErrors();
            }
            else $this->_json_result['message'] = array('Update failed');
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
    private function checkLogin($username, $password, $remember_me = null) {

        $identity = new UserIdentity($username, $password);
        $identity->authenticate();
        if ($identity->errorCode === UserIdentity::ERROR_NONE) {
            return $identity;
        } else return null;
    }

}