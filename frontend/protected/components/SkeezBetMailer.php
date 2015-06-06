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
    private $_email_template_forgot_password;
    public function __construct(){
        $this->_mailer_config = Yii::app()->params['php_mailer'];
        $this->_mailer = new PHPMailer();
        $this->_mailer->isSMTP();
        $this->_mailer->Mailer       = $this->_mailer_config['protocol'];
        $this->_mailer->Host         = $this->_mailer_config['host'];
        $this->_mailer->SMTPAuth     = true;
        $this->_mailer->Port         = $this->_mailer_config['port'];
        $this->_mailer->Username     = $this->_mailer_config['username'];
        $this->_mailer->Password     = $this->_mailer_config['password'];
        $this->_mailer->SMTPSecure   = $this->_mailer_config['secure_protocol'];
        $this->_mailer->From         = $this->_mailer_config['from_header_email'];
        $this->_mailer->FromName     = $this->_mailer_config['from_header_name'];
        $this->_mailer->WordWrap = 50;
        $this->_mailer->isHTML(true);
        $this->_email_template = Yii::app()->params['emailTemplates'];
        $this->_email_template_forgot_password = Yii::app()->params['emailTemplatesForgotPassword'];
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
    public function sendForGotPasswordEmail($to, $account) {
        $forgot_email_content = $this->fetchEmailTemplate($this->_email_template_forgot_password['welcome']['template']);
        if (!$forgot_email_content){
            return false;
        }

        $match_cases = array(
            '[FIRST_NAME]' => $account['first_name'],
            '[LINK]' => $account['link']
        );

        $forgot_email_content = $this->parseEmailVariable($match_cases, $forgot_email_content);
        $this->addData($to, $this->_email_template_forgot_password['welcome']['subject'], $forgot_email_content);

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