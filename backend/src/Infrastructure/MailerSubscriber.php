<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[AsService]
final class MailerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%env(string:MAILER_FROM_EMAIL)%')]
        private readonly string $fromEmail,
        #[Autowire('%env(string:MAILER_FROM_NAME)%')]
        private readonly string $fromName
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'processDefaultFrom',
        ];
    }

    /**
     * Всем письмам добавляет тему письма, почту отправителя
     */
    public function processDefaultFrom(MessageEvent $messageEvent): void
    {
        $email = $messageEvent->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($this->fromEmail, $this->fromName));
        $email->subject(sprintf('%s. %s', $this->fromName, $email->getSubject()));
    }
}
