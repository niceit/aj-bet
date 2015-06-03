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
}