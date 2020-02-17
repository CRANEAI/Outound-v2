<?php

namespace MailPoet\Mailer\WordPress;

use Html2Text\Html2Text;
use MailPoet\Mailer\Mailer;
use MailPoet\Mailer\MetaInfo;
use MailPoet\Subscribers\SubscribersRepository;

if (!class_exists('PHPMailer')) {
  require_once ABSPATH . WPINC . '/class-phpmailer.php';
}

class WordPressMailer extends \PHPMailer {

  /** @var Mailer */
  private $mailer;

  /** @var Mailer */
  private $fallbackMailer;

  /** @var MetaInfo */
  private $mailerMetaInfo;

  /** @var SubscribersRepository */
  private $subscribersRepository;

  public function __construct(
    Mailer $mailer,
    Mailer $fallbackMailer,
    MetaInfo $mailerMetaInfo,
    SubscribersRepository $subscribersRepository
  ) {
    parent::__construct(true);
    $this->mailer = $mailer;
    $this->fallbackMailer = $fallbackMailer;
    $this->mailerMetaInfo = $mailerMetaInfo;
    $this->subscribersRepository = $subscribersRepository;
  }

  public function send() {
    // We need this so that the \PHPMailer class will correctly prepare all the headers.
    $this->Mailer = 'mail'; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps

    // Prepare everything (including the message) for sending.
    $this->preSend();

    $email = $this->getEmail();
    $address = $this->formatAddress($this->getToAddresses());
    $subscriber = $this->subscribersRepository->findOneBy(['email' => $address]);
    $extraParams = [
      'meta' => $this->mailerMetaInfo->getWordPressTransactionalMetaInfo($subscriber),
    ];

    $sendWithMailer = function ($mailer) use ($email, $address, $extraParams) {
      $result = $mailer->send($email, $address, $extraParams);
      if (!$result['response']) {
        throw new \Exception($result['error']->getMessage());
      }
    };

    try {
      $sendWithMailer($this->mailer);
    } catch (\Exception $e) {
      try {
        $sendWithMailer($this->fallbackMailer);
      } catch (\Exception $fallbackMailerException) {
        // throw exception passing the original (primary mailer) error
        throw new \phpmailerException($e->getMessage(), $e->getCode(), $e);
      }
    }
    return true;
  }

  private function getEmail() {
    $email = [
      'subject' => $this->Subject, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
      'body' => [],
    ];

    if ($this->ContentType === 'text/plain') { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
      $email['body']['text'] = $this->Body; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
    } elseif ($this->ContentType === 'text/html') { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
      $text = @Html2Text::convert(strtolower($this->CharSet) === 'utf-8' ? $this->Body : utf8_encode($this->Body)); // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
      $email['body']['text'] = $text;
      $email['body']['html'] = $this->Body; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
    } else {
      throw new \phpmailerException('Unsupported email content type has been used. Please use only text or HTML emails.');
    }
    return $email;
  }

  private function formatAddress($wordpressAddress) {
    $data = $wordpressAddress[0];
    $result = [
      'address' => $data[0],
    ];
    if (!empty($data[1])) {
      $result['full_name'] = $data[1];
    }
    return $result;
  }
}
