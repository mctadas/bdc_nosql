<?php

class Bb4w_View_Helper_FlashMessages extends Zend_View_Helper_Abstract
{

    public function flashMessages()
    {
        $result = array();
        $messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $messages = $messenger->getMessages();
        $categorizedMessages = array();
        for ($i = 0; $i < count($messages); $i++) {
            if (isset($messages[$i][0]) && isset($messages[$i][1])) {
                if (is_array($messages[$i][1])) {
                    foreach ($messages[$i][1] as $message) {
                        $categorizedMessages[$messages[$i][0]][] = $message;
                    }
                } else {
                    $categorizedMessages[$messages[$i][0]][] = $messages[$i][1];
                }
            }
            
            if (isset($messages[$i][2])) {
                $result['fieldId'] = $messages[$i][2];
            }
        }
        return $categorizedMessages;
    }

}