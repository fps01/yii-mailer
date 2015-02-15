<?php

/**
 * Class Mailer
 * Yii 1.1.x mailer extension based on Swift Mailer https://github.com/swiftmailer/swiftmailer
 * @author Pavel Fedotov <fps.06@mail.ru>
 * @copyright 2015 Pavel Fedotov
 * @link https://github.com/fps01/yii-mailer
 * @package mailer
 * @version 1.0.0
 */
class Mailer extends CApplicationComponent
{
    /** @var $mailer Swift_Mailer */
    protected $mailer;
    /** @var $type string type of email transport (mail, sendmail or smtp) */
    public $type = 'mail';
    /** @var $address string smtp server address */
    public $address;
    /** @var $port int smtp server port */
    public $port;
    /** @var $security null|string  */
    public $security;
    /** @var $username string smtp username */
    public $username;
    /** @var $password string smtp password */
    public $password;
    /** @var $sendmailCommand string path to sendmail command */
    public $sendmailCommand;
    /** @var $sender string|array message sender name */
    public $sender;

    /**
     * Create message.
     * @param $addresses string
     * @param $subject string
     * @return Swift_Mime_SimpleMessage
     */
    private function createMessage($addresses, $subject)
    {
        return Swift_Message::newInstance($subject)
            ->setFrom($this->sender)
            ->setTo($addresses);
    }

    /**
     * Add attachment files to email.
     * @param $message string
     * @param $attachments array of file paths to attach to email
     * @return mixed
     */
    private function addAttachments($message, $attachments)
    {
        foreach ($attachments as $attachment) {
            $message->attach(Swift_Attachment::fromPath($attachment));
        }
        return $message;
    }

    /**
     * Extension initialization.
     */
    public function init()
    {
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'lib'
            . DIRECTORY_SEPARATOR . 'swift_required.php';
        spl_autoload_register(array('YiiBase', 'autoload'));
        switch ($this->type) {
            case 'smtp':
                $transport = Swift_SmtpTransport::newInstance($this->address, $this->port, $this->security)
                    ->setUsername($this->username)
                    ->setPassword($this->password);
                break;
            case 'sendmail':
                $transport = Swift_SendmailTransport::newInstance($this->sendmailCommand);
                break;
            default:
                $transport = Swift_MailTransport::newInstance();
        }
        $this->mailer = Swift_Mailer::newInstance($transport);
    }

    /**
     * Send message with plain, html texts and attachment files.
     * @param array|string $addresses addresses list
     * @param string $subject email subject
     * @param string $textBody plain message text
     * @param string $htmlBody alternative html message with tags
     * @param array $attachments list of file paths to attach to email
     * @return int number of sent messages
     */
    public function send($addresses, $subject, $textBody, $htmlBody, $attachments = array())
    {
        $message = $this->createMessage($addresses, $subject)->setBody($textBody)->addPart($htmlBody, 'text/html');
        $message = $this->addAttachments($message, $attachments);
        return $this->mailer->send($message);
    }

    /**
     * Send message with plain text and attachment files.
     * @param array|string $addresses addresses list
     * @param string $subject email subject
     * @param string $body plain message text
     * @param array $attachments list of file paths to attach to email
     * @return int number of sent messages
     */
    public function sendText($addresses, $subject, $body, $attachments = array())
    {
        $message = $this->createMessage($addresses, $subject)->setBody($body, 'text/plain');
        $message = $this->addAttachments($message, $attachments);
        return $this->mailer->send($message);
    }

    /**
     * Send message with html text and attachment files.
     * @param array|string $addresses addresses list
     * @param string $subject email subject
     * @param string $body html message with tags
     * @param array $attachments list of file paths to attach to email
     * @return int number of sent messages
     */
    public function sendHtml($addresses, $subject, $body, $attachments = array())
    {
        $message = $this->createMessage($addresses, $subject)->setBody($body, 'text/html');
        $message = $this->addAttachments($message, $attachments);
        return $this->mailer->send($message);
    }
}
