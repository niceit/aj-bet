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
		$this->render('index');
	}

    /*
     * User registration action
     * */
    public function actionRegister(){
        if (Yii::app()->request->isPostRequest){
            $data = Yii::app()->request->getPost('Accounts');
            $Account = new Accounts();
            $Account->attributes = $data;

            //Validate registration data
            if ($Account->validate()){
                if (strcmp($data['password'], $data['retype_password'])){
                    $this->_json_result['message'] = array('Password confirmation does not match');
                    $now = date('d-m-Y');
                    $password_encrypt = md5($data['username'] . $now);
                    $Account->setAttribute('password', $password_encrypt);
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