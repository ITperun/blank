<?php

declare(strict_types=1);

namespace App\MailSender;

use Nette\Application\UI\TemplateFactory;
use Nette\Mail\Message;
use Nette\Mail\Mailer;

class MailSender
{
    public function __construct(
        private Mailer $mailer,
        private TemplateFactory $templateFactory
    ) {}

    // Метод создания письма
    public function createNotificationEmail(string $recipient, string $name, string $item, string $note): Message
    {
        // Создаем шаблон
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/email.latte');
        
        // Передача в шаблон
        $template->name = $name;
        $template->item = $item;
        $template->note = $note;

        // Создаем объект сообщения
        $mail = new Message;
        $mail->setFrom('rucickaigor@seznam.cz', 'E-shop MOP'); // Укажи здесь тот же email, что в конфиге
        $mail->addTo($recipient);
        $mail->setSubject('Nová objednávka: ' . $item);
        $mail->setHtmlBody((string) $template);

        return $mail;
    }

    // Универсальный метод отправки
    public function sendEmail(Message $mail): void
    {
        try {
            $this->mailer->send($mail);
        } catch (\Exception $e) {
            // Логируем ошибку или выбрасываем дальше, если нужно
            // Tracy\Debugger::log($e);
        }
    }
}