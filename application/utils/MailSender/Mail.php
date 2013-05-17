<?php

namespace MailSender;

use \Zend_View;
use \Zend_Mail;
use \Zend_Mime;

class Mail
{
    /**
     * @param string or array $emails
     * @param string $subject
     * @param string $templateName
     * @param array $templateData
     * @param array $attachments with keys ("document_body", "content_type", "filename")
     * @return bool
     */
    public function send($emails, $subject, $templateName, $templateData = array(), $attachments = array())
    {
        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH . '/layouts/scripts/email-templates/');
        $view->data = $templateData;
        $html = $view->render($templateName);

        $mail = new Zend_Mail('utf-8');
        $mail->setFrom('dont-reply@gaumina.lt', 'Mail');
        $mail->setSubject($subject);
        $mail->setBodyHtml($html);
        
        if (!empty($attachments)) {
            foreach($attachments as $value) {
                $attachment = $mail->createAttachment( $value["document_body"] );
                $attachment->type = $value["content_type"];
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                $attachment->filename = $value["filename"];
            }
        }
        
        if (is_array($emails)) {
            foreach($emails as $email) {
                $mail->addTo($email);
                $mail->send();
            }
        } else {
            $mail->addTo($emails);
            $mail->send();
        }
        
        return true;
    }
}
