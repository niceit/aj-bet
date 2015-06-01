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
            $remember_me = Yii::app()->request->getPost('remember_me');
            $is_login = true;
            // check username and password
            if (empty($username)){
                $is_login = false;
                $this->_json_result['message'] = array('Invalid login information');
            }
            if ($is_login && empty($password)){
                $is_login = false;
                $this->_json_result['message'] = array('Invalid login information');
            }

            // check login
            if ($is_login) {
                $result = $this->checkLogin($username, $password, $remember_me);
                if ($result!=false) {
                    if($result->getActive()==1)
                    {
                        $account = array(
                            'id'            => $result->getId(),
                            'first_name'    => $result->getFirstname(),
                            'last_name'     => $result->getLastname()

                        );
                        $this->_json_result['message'] = array('account'=>$account,'message'=>'Logged in successfully');
                    }
                    else
                        $this->_json_result['message'] = array('Please active your account before using');

                } else
                    $this->_json_result['message'] = array('Invalid login information');
            }
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
            $duration = !empty($remember_me) ? (3600 * 24 * 30) : (3600 * 24 * 1); // 30 days
            Yii::app()->user->login($identity, $duration);
            return $identity;
        } else
            return false;
    }

}