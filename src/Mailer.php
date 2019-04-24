<?php

namespace robertklein\mailgun;

use Yii;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use \Mailgun\Mailgun;

/**
 * Mailer implements a mailer based on Mailgun.
 *
 * To use Mailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'mailer' => [
 *         'class' => 'robertklein\mailgun\Mailer',
 *         'key' => 'key-example',
 *         'domain' => 'mg.example.com',
 *     ],
 *     ...
 * ],
 * ~~~
 *
 * To send an email, you may use the following code:
 *
 * ~~~
 * Yii::$app->mailer->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ~~~
 */
class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = 'robertklein\mailgun\Message';

    /**
     * @var string Mailgun API credentials.
     */
    public $key;

    /**
     * @var string Mailgun domain.
     */
    public $domain;

    /**
     * @var Mailgun Mailgun instance.
     */
    private $_mailgun;
    
    /**
     * @var Object Mailgun send response containing `http_response_code` (with `id` and `message`) 
     * and `http_response_body`.
     */
    private $response;

    /**
     * @return Mailgun Mailgun instance.
     */
    public function getMailgun()
    {
        if (!is_object($this->_mailgun)) {
            $this->_mailgun = $this->createMailgun();
        }

        return $this->_mailgun;
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        Yii::info('Sending email', __METHOD__);

        $this->response = $this->getMailgun()->post(
            "{$this->domain}/messages",
            $message->getMessageBuilder()->getMessage(),
            $message->getMessageBuilder()->getFiles());

        return true;
    }

    /**
     * Creates Mailgun instance.
     * @return Mailgun Mailgun instance.
     * @throws InvalidConfigException if required params are not set.
     */
    protected function createMailgun()
    {
        if (!$this->key) {
            throw new InvalidConfigException('Mailer::key must be set.');
        }
        if (!$this->domain) {
            throw new InvalidConfigException('Mailer::domain must be set.');
        }
        return new Mailgun($this->key);
    }

    /**
     * @return Object Mailgun send response.
     */
    public function getResponse()
    {
        return $this->response;
    }
}
