<?php
/**
 * SkeezBetMailer.php
 * Author: Tran IT
 * Date: 06/03/2015
 * Description : Mailer processing for SkeezBet Mail Serive
 */

class SkeezBetMailer{

    private $_mailer;
    private $_mailer_config;
    private $_email_template;

    public function __construct(){
        $this->_mailer_config = Yii::app()->params['php_mailer'];
        $this->_mailer = new PHPMailer();
        $this->_mailer->isMail();
        $this->_mailer->SMTPAuth     = false;
        $this->_mailer->From         = $this->_mailer_config['from_header_email'];
        $this->_mailer->FromName     = $this->_mailer_config['from_header_name'];
        $this->_mailer->WordWrap = 50;
        $this->_mailer->isHTML(true);
        $this->_email_template = Yii::app()->params['emailTemplates'];
    }

    /*
     * Send welcome email
     * */
    public function sendWelcomeEmail($to, $account) {
        $welcome_email_content = $this->fetchEmailTemplate($this->_email_template['welcome']['template']);

        if (!$welcome_email_content){
            return false;
        }

        $match_cases = array(
            '[FIRST_NAME]' => $account['first_name'],
            '[USERNAME]' => $account['username'],
            '[PASSWORD]' => $account['password']
        );

        $welcome_email_content = $this->parseEmailVariable($match_cases, $welcome_email_content);
        $this->addData($to, $this->_email_template['welcome']['subject'], $welcome_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }
    /*
    * Send forgot password email
    * */
    public function sendForgotPasswordEmail($to, $account) {
        $forgot_email_content = $this->fetchEmailTemplate($this->_email_template['forgot']['template']);
        if (!$forgot_email_content){
            return false;
        }

        $match_cases = array(
            '[FIRST_NAME]' => $account['first_name'],
            '[LINK]' => $account['link']
        );

        $forgot_email_content = $this->parseEmailVariable($match_cases, $forgot_email_content);
        $this->addData($to, $this->_email_template['forgot']['subject'], $forgot_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }

    /*
    * Send add friend
    * */
    public function sendAddFriendEmail($to, $account, $friend) {
        $add_friend_email_content = $this->fetchEmailTemplate($this->_email_template['email_friend']['template']);
        if (!$add_friend_email_content){
            return false;
        }
        var_dump($add_friend_email_content);
        die('dddd');
        $match_cases = array(
            '[ACCOUNT_FIRST_NAME]' => $account['account_first_name'],
            '[FRIENDS_FIRST_NAME]' => $friend['friends_first_name']
        );

        $add_friend_email_content = $this->parseEmailVariable($match_cases, $add_friend_email_content);
        $this->addData($to, $this->_email_template['email_friend']['subject'], $add_friend_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }

    /*
   * Send add friend
   * */
    public function sendApproveAddFriendEmail($to, $account, $friend) {
        $add_friend_email_content = $this->fetchEmailTemplate($this->_email_template['approve_email_friend']['template']);

        if (!$add_friend_email_content){
            return false;
        }

        $match_cases = array(
            '[ACCOUNT_FIRST_NAME]' => $account['account_first_name'],
            '[FRIENDS_FIRST_NAME]' => $friend['friends_first_name']
        );

        $add_friend_email_content = $this->parseEmailVariable($match_cases, $add_friend_email_content);
        $this->addData($to, $this->_email_template['approve_email_friend']['subject'], $add_friend_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }

    /*
  * Send add friend
  * */
    public function sendAddBetsFriendEmail($to, $account) {
        $bets_friend_email_content = $this->fetchEmailTemplate($this->_email_template['bets_email_friend']['template']);

        if (!$bets_friend_email_content){
            return false;
        }

        $match_cases = array(
            '[ACCOUNT_FIRST_NAME]' => $account['account_first_name'],
            '[FRIENDS_FIRST_NAME]' => $account['friends_first_name'],
            'NAME_HOME'            => $account['home']['name'],
            'LOGO_HOME'            => $account['home']['logo'],
            'SCORE_HOME'            => $account['home']['score'],

            'NAME_OPPONENT'        => $account['opponent']['name'],
            'LOGO_OPPONENT'        => $account['opponent']['logo'],
            'SCORE_OPPONENT'       => $account['home']['score']

        );

        $bets_friend_email_content = $this->parseEmailVariable($match_cases, $bets_friend_email_content);
        $this->addData($to, $this->_email_template['bets_email_friend']['subject'], $bets_friend_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }

    /*
   * Send add approve bets
   * */
    public function sendApproveBetsFriendEmail($to, $account) {
        $add_friend_email_content = $this->fetchEmailTemplate($this->_email_template['approve_bets_email_friend']['template']);

        if (!$add_friend_email_content){
            return false;
        }

        $match_cases = array(
            '[ACCOUNT_FIRST_NAME]' => $account['account_first_name'],
            '[FRIENDS_FIRST_NAME]' => $account['friends_first_name']
        );

        $add_friend_email_content = $this->parseEmailVariable($match_cases, $add_friend_email_content);
        $this->addData($to, $this->_email_template['approve_bets_email_friend']['subject'], $add_friend_email_content);

        if ($this->_mailer->send()){
            return true;
        }
        else return false;
    }

    /*
     * Set data for mailer
     * */
    public function addData($to, $subject, $body) {

        //Set target email
        if (is_array($to)) {
            foreach ($to as $recipient) {
                $this->_mailer->addCC($recipient);
            }
        }
        else $this->_mailer->addCC($to);

        //Set mail's elements
        $this->_mailer->Subject = $subject;
        $this->_mailer->Body =  $body;
    }

    /*
     * Send mailer
     * */
    public function sendMailer($to = null, $subject = null, $body = null) {

        /*
         * Checking for available elements set
         * */
        if (!empty($to)){
            if (is_array($to)) {
                foreach ($to as $recipient) {
                    $this->_mailer->addCC($recipient);
                }
            }
            else $this->_mailer->addCC($to);
        }

        if (!empty($subject)) {
            $this->_mailer->Subject = $subject;
        }

        if (!empty($body)) {
            $this->_mailer->Body = $body;
        }

        return ($this->_mailer->send());
    }

    /*
     * Parse email content variables
     * */
    public function parseEmailVariable($match_cases, $email_content) {
        foreach ($match_cases as $find => $replace) {
            $email_content = str_replace($find, $replace, $email_content);
        }
        return $email_content;
    }

    /*
     * Fetch email templates
     * */
    private function fetchEmailTemplate($template_name) {
        $ROOT = $_SERVER['DOCUMENT_ROOT'];
        $email_template_dir = $ROOT . '/frontend/protected/views/emails/';

        $target_file = $email_template_dir . $template_name . '.php';
        if (!file_exists($target_file)) {
            return null;
        }
        else{
            $content = Yii::app()->controller->renderFile ($target_file, array(), true);
            return $content;
        }
    }
}