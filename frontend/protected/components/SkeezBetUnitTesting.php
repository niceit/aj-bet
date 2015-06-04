<?php
/**
 * SkeezBetUnitTest.php
 * Author: TranIT
 * Date: 06/04/2015
 * Description: Provide testing functionally
 */

class SkeezBetUnitTesting{

    //Mailing template testing
    public function emailTemplateTesting($template_name){
        $ROOT = $_SERVER['DOCUMENT_ROOT'];
        $email_template_dir = $ROOT . '/frontend/protected/views/emails/';
        $target_file = $email_template_dir . $template_name . '.php';
        if (!file_exists($target_file)) {
            Yii::app()->end ('Invalid template name');
        }
        else{
            $content = Yii::app()->controller->renderFile ($target_file, array(), true);
            $SkeezBetMailer = new SkeezBetMailer();
            Yii::app()->end ($SkeezBetMailer->parseEmailVariable(array('[FIRST_NAME]' => 'Tran'), $content));
        }
    }
}