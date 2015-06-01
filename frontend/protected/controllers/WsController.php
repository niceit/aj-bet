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
                    $password_encrypt = md5($data['username'] . $now);
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
     * Send back request to client
     * */
    private function sendResponse($type, $data, $json_data = true){
        header ("Content-Type: {$type}");
        if ($json_data){
            Yii::app()->end (json_encode($data));
        }
        else Yii::app()->end ($data);
    }
}