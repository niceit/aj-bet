<?php

class WsController extends Controller
{
    //Json data return back to client
    private $_json_result;

    public function filters(){
        $this->_json_result = array('status' => 0, 'message' => array('Nothing performed'));
    }

    //Support json decode debug
    private static function parsePostJsonRequest ($request) {
        $debug_mode = Yii::app()->request->getQuery ('debug');

        if ($debug_mode) {
            return $request;
        }
        else return json_decode ($request, true);
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

            $data = self::parsePostJsonRequest ($data);

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
                    $link_active = 'http://www.betskeez.com/forgot?token='.$activation_token;

                    $SkeezBetMailer = new SkeezBetMailer();
                    $account_mail = array(
                        'first_name'    => $account->first_name,
                        'username'      => $account->username,
                        'link'          => $link_active
                    );

                    $sendMail = $SkeezBetMailer->sendForgotPasswordEmail($account->email, $account_mail);
                    if($sendMail){
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
    * List BetCategories action
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
    * List BetCategories action
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
   * List Leagues
   * */
    public function actionLeagues(){
        if (Yii::app()->request->isPostRequest) {
            $Leagues = array();
            $category_id = Yii::app()->request->getPost('category_id');
            if (empty($category_id)){
                $this->_json_result['message'] = array('Invalid request category');
                $this->sendResponse("application/json", $this->_json_result);
            }

            if (SkeezBetSubCategories::model()->findByPk($category_id)) {
                $leagues = SkeezLeagues::model()->findAll('category_id = ' . $category_id);
                if (!empty($leagues)) {
                    foreach ($leagues as $league) {
                        $Leagues[] = array(
                            'id'    => $league->id,
                            'name'  => $league->name
                        );
                    }

                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Leagues loaded successfully');
                    $this->_json_result['Leagues'] = array($Leagues);
                }
                else $this->_json_result['message'] = array('There is no available league');
            }
            else $this->_json_result['message'] = array('Category was not found');
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    * List available matches
    * */
    public function actionMatches(){
        if (Yii::app()->request->isPostRequest) {
            $TeamMatches = array();
            $League = Yii::app()->request->getPost('League');

            $League = self::parsePostJsonRequest ($League);

            if (empty($League)){
                $this->_json_result['message'] = array('Invalid League request');
                $this->sendResponse("application/json", $this->_json_result);
            }

            foreach($League as $league){
                $getTeamMatches = SkeezTeamMatches::model()->getTeamMatches($league);
                foreach($getTeamMatches as $teamMatches){
                    $homeMatches =  SkeezTeams::model()->findByPk($teamMatches['home']);
                    $opponentMatches = SkeezTeams::model()->findByPk($teamMatches['opponent']);

                    $TeamMatches[] = array(
                        'home'          =>  array(
                                        'name'  =>  $homeMatches->name,
                                        'logo'  =>  Yii::app()->params['base_url'] . $homeMatches->logo
                                    ),
                        'opponent'      =>  array(
                                        'name'  =>  $opponentMatches->name,
                                        'logo'  =>  Yii::app()->params['base_url'] . $opponentMatches->logo
                                    ),
                        'time_start'    =>  $teamMatches['match_time']
                    );
                }
            }
            if (!empty($TeamMatches)){
                $this->_json_result['status'] = 1;
                $this->_json_result['message'] = array('TeamMatches successfully loaded');
                $this->_json_result['TeamMatches'] = array($TeamMatches);
            }
            else $this->_json_result['message'] = array('There is no result');
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
      *  AddFriend
      * */
    public function actionAddFriend(){
        if (Yii::app()->request->isPostRequest) {

            $account_id = Yii::app()->request->getPost ('account_id');
            $friend_id = Yii::app()->request->getPost ('friend_id');
            $message = Yii::app()->request->getPost ('message');

            $skeezFriend = SkeezFriends::model()->find ('friend_id = ' . $friend_id . ' AND account_id = ' . $account_id);

            if (!empty($skeezFriend)) {
                $this->_json_result['message'] = array('You have added this friend before.');
                $this->sendResponse ("application/json", $this->_json_result);
            }

            $account =  Accounts::model()->findByPk ($account_id);
            $friend =  Accounts::model()->findByPk ($friend_id);


            if (!empty($account) &&  !empty($friend) && ($account_id != $friend_id)) {

                $addFriend = new SkeezFriends();
                $addFriend->setAttribute('account_id', $account_id);
                $addFriend->setAttribute('friend_id', $friend_id);
                $addFriend->setAttribute('message', $message);
                $addFriend->setAttribute('approve', 0);
                $addFriend->setAttribute('created', new CDbExpression('NOW()'));

                if ($addFriend->save()) {
                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Your friend request has been sent.');

                    $SkeezBetMailer = new SkeezBetMailer();

                    $sendMail = $SkeezBetMailer->sendAddFriendEmail ($friend->email, $account->first_name, $friend->first_name);

                    if (!$sendMail) {
                        $this->_json_result['message'] = array ('Could not send email');
                    }

                }
                else $this->_json_result['message'] = $addFriend->getErrors();
            }
            else $this->_json_result['message'] = array('Friend account was not found.');
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
     *  Approve Add friend request
     * */
    public function actionApproveFriend() {
        if (Yii::app()->request->isPostRequest) {

            $account_id = Yii::app()->request->getPost ('account_id');
            $friend_id = Yii::app()->request->getPost ('friend_id');
            $accept_mode = Yii::app()->request->getPost ('accept');

            $friend = SkeezFriends::model()->find('friend_id = ' . $friend_id . ' AND account_id = ' . $account_id);

            if (empty($friend)){
                $this->_json_result['message'] = array('Friend request session was not found.');
                $this->sendResponse("application/json", $this->_json_result);
            }

            if($friend->approve == 1) {
                $this->_json_result['message'] = array('Friend request had been approved.');
                $this->sendResponse("application/json", $this->_json_result);
            }

            if ($account_id != $friend_id ) {

                switch ($accept_mode) {
                    case 1 : //Accept request
                        $friend->setAttribute ('approve', 1);
                        $message = 'You approved friend request';
                        break;
                    case 2 : //Decline request
                        $friend->setAttribute ('approve', 2);
                        $message = 'You declined friend request';
                        break;
                    default :
                        $this->_json_result['message'] = array('Your operation was not allowed');
                        $this->sendResponse ("application/json", $this->_json_result);
                        break;
                }

                $friend->setAttribute('modified', new CDbExpression('NOW()'));

                if ($friend->save()) {
                    $account =  Accounts::model()->findByPk ($account_id);
                    $friend =  Accounts::model()->findByPk ($friend_id);

                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array($message);

                    $SkeezBetMailer = new SkeezBetMailer();

                    $sendMail = $SkeezBetMailer->sendApproveAddFriendEmail($account->email, $account->first_name, $friend->first_name, $accept_mode);

                    if (!$sendMail) {
                        $this->_json_result['message'] = array ('Could not send email');
                    }
                }
                else $this->_json_result['message'] = $friend->getErrors();
            }
            else $this->_json_result['message'] = 'Your request was not allowed';
        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    *  Bets between user
    * */
    public function actionBet()
    {
        if (Yii::app()->request->isPostRequest) {

            $account_id = Yii::app()->request->getPost('account_id');
            $friend_id = Yii::app()->request->getPost('friend_id');
            $match_id = Yii::app()->request->getPost('match_id');
            $score_1 = Yii::app()->request->getPost('score_1');
            $score_2 = Yii::app()->request->getPost('score_2');
            $is_public = Yii::app()->request->getPost('is_public');

            $skeezBet = new SkeezBets();
            $skeezBet->setAttribute ('account_id', $account_id);
            $skeezBet->setAttribute ('friend_id', $friend_id);
            $skeezBet->setAttribute ('match_id', $match_id);
            $skeezBet->setAttribute ('score_1', $score_1);
            $skeezBet->setAttribute ('score_2', $score_2);
            $skeezBet->setAttribute ('approve', 0);
            $skeezBet->setAttribute ('is_public', $is_public);
            $skeezBet->setAttribute( 'created', new CDbExpression('NOW()'));

            if ($skeezBet->validate()) {

                $account =  Accounts::model()->findByPk ($account_id);
                $friend =  Accounts::model()->findByPk ($friend_id);

                if (!empty($account) && !empty($friend)){
                    $bet = SkeezBets::model()->find('friend_id = ' . $friend_id . ' AND account_id = ' . $account_id);

                    if (!empty($bet)>0){
                        $this->_json_result['message'] = array('You have sent this match to this friend before.');
                        $this->sendResponse("application/json", $this->_json_result);
                    }
                }
                else{
                    $this->_json_result['message'] = array('Your account or friend account was not found.ß');
                    $this->sendResponse("application/json", $this->_json_result);
                }


                if ($skeezBet->save()) {
                    $this->_json_result['status'] = 1;
                    $this->_json_result['message'] = array('Bet has been sent.');

                    $matchs = SkeezMatches::model()->findByPk ($match_id);
                    $matchs_teams = SkeezTeamMatches::model()->findByPk ($matchs->team_match);

                    $home = SkeezTeams::model()->findByPk ($matchs_teams->home);
                    $opponent = SkeezTeams::model()->findByPk ($matchs_teams->opponent);

                    $SkeezBetMailer = new SkeezBetMailer();

                    $account_mail = array(
                        'home'               => array(
                            'name'  => $home->name,
                            'logo'  =>  Yii::app()->params['base_url'] . $home->logo,
                            'score' => $score_1
                        ),
                        'opponent'               => array(
                            'name'  => $opponent->name,
                            'logo'  =>  Yii::app()->params['base_url'] . $opponent->logo,
                            'score' => $score_2
                        ),
                        'account_first_name'    => $account->first_name,
                        'friends_first_name'    => $friend->first_name
                    );

                    $sendMail = $SkeezBetMailer->sendAddBetsFriendEmail($friend->email, $account_mail);

                    if(!$sendMail){
                        $this->_json_result['message'] = array ('Could not send email');
                    }
                }
                else $this->_json_result['message'] = $skeezBet->getErrors();
            }
            else $this->_json_result['message'] = $skeezBet->getErrors();

        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
      *  Bets account
      * */
    public function actionApproveBet()
    {
        if (Yii::app()->request->isPostRequest) {

            $bet_id = Yii::app()->request->getPost('bet_id');
            $accept_mode = Yii::app()->request->getPost('accept');

            $bet = SkeezBets::model()->findByPk ($bet_id);

            if (!empty($bet)) {
                $this->_json_result['message'] = array('Bet session was not found.');
                $this->sendResponse ("application/json", $this->_json_result);
            }

            //Checking for approve or decline the bet
            switch ($accept_mode) {
                case 1 : //Accept
                    $bet->setAttribute('approve', 1);
                    $message = 'Bet has been approved';
                    break;
                case 2 : //Decline
                    $bet->setAttribute('approve', 2);
                    $message = 'Bet has been declined';
                    break;
                default :
                    $this->_json_result['message'] = array('Your operation was not allowed.');
                    $this->sendResponse ("application/json", $this->_json_result);
                    break;
            }

            $bet->setAttribute('modified', new CDbExpression('NOW()'));
            if ($bet->save()) {
                $this->_json_result['status'] = 1;

                $this->_json_result['message'] = array ($message);

                $account =  Accounts::model()->findByPk($bet->account_id);
                $friend =  Accounts::model()->findByPk($bet->friend_id);

                $SkeezBetMailer = new SkeezBetMailer();

                $account_mail = array(
                    'account_first_name'    => $account->first_name,
                    'friends_first_name'    => $friend->first_name
                );

                $sendMail = $SkeezBetMailer->sendApproveBetsFriendEmail ($account->email, $account_mail, $accept_mode);
                if(!$sendMail){
                    $this->_json_result['message'] = array ('Could not send email');
                }
            }else $this->_json_result['message'] = $bet->getErrors();

        }
        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
   *  Get  List public bets
   * */
    public function actionListPublicBets()
    {
        $bet_public = array();
        $subcategory = SkeezBetSubCategories::model()->findAll(array('order' => 'parent_category_id ASC'));
        $arr_id_best = array();
        $bets_result = SkeezBetResults::model()->findAll();
        foreach($bets_result as $bet)
            $arr_id_best[] =   $bet->bet_id;

        if(!empty($subcategory))
        {
            foreach($subcategory as $cate){
                $bets = SkeezBets::model()->getListPublicBets($cate->id,$arr_id_best);

                if(!empty($bets)){
                    $bets_temp = array();
                    foreach ($bets as $bet) {
                        $account =  Accounts::model()->findByPk($bet['account_id']);
                        $friend =  Accounts::model()->findByPk($bet['friend_id']);

                        $bets_temp[] = array(
                            'id'            => $bet['id'],
                            'account'       => array(
                                'id'            => $account->id,
                                'first_name'    => $account->first_name,
                                'avatar'        => Yii::app()->params['base_url'].$account->avatar
                            ),
                            'friend'       => array(
                                'id'            => $friend->id,
                                'first_name'    => $friend->first_name,
                                'avatar'        => Yii::app()->params['base_url'].$friend->avatar
                            )
                        );
                    }
                    $bet_public[] = array(
                        'category'    => array(
                                        'id'    => $cate->id,
                                        'name'  => $cate->name
                                        ),
                        'bets'  => $bets_temp
                    );
                }
            }
        }

        if(!empty($bet_public)){
            $this->_json_result['status'] = 1;
            $this->_json_result['message'] = array ('Bets successfully loaded');
            $this->_json_result['Bets'] = array($bet_public);
        }else $this->_json_result['message'] = array ('There is no bet available');

        $this->sendResponse("application/json", $this->_json_result);
    }

    /*
    *  Get List Private bets
    * */
    public function actionListPrivateBets()
    {
        if (Yii::app()->request->isPostRequest) {
            $account_id = Yii::app()->request->getPost('account_id');
            $bet_private = array();
            $subcategory = SkeezBetSubCategories::model()->findAll(array('order' => 'parent_category_id ASC'));

            $arr_id_best = array();
            $bets_result = SkeezBetResults::model()->findAll();
            foreach($bets_result as $bet)
                $arr_id_best[] =   $bet->bet_id;

            if(!empty($subcategory))
            {
                foreach($subcategory as $cate){
                    $bets = SkeezBets::model()->getListPrivateBets($cate->id,$account_id,$arr_id_best);

                    if(!empty($bets)){
                        $bets_temp = array();
                        foreach ($bets as $bet) {
                            $account =  Accounts::model()->findByPk($bet['account_id']);
                            $friend =  Accounts::model()->findByPk($bet['friend_id']);
                            $bets_temp[] = array(
                                'id'            => $bet['id'],
                                'account'       => array(
                                    'id'            => $account->id,
                                    'first_name'    => $account->first_name,
                                    'avatar'        => Yii::app()->params['base_url'].$account->avatar
                                ),
                                'friend'       => array(
                                    'id'            => $friend->id,
                                    'first_name'    => $friend->first_name,
                                    'avatar'        => Yii::app()->params['base_url'].$friend->avatar
                                )
                            );
                        }
                        $bet_private[] = array(
                            'category'    => array(
                                'id'    => $cate->id,
                                'name'  => $cate->name
                            ),
                            'bets'  => $bets_temp
                        );
                    }
                }
            }
            if(!empty($bet_private)){
                $this->_json_result['status'] = 1;
                $this->_json_result['message'] = array ('Bets private successfully loaded');
                $this->_json_result['Bets'] = array($bet_private);
            }else $this->_json_result['message'] = array ('There is no bet private available');


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