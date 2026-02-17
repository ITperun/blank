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
        $mail = new Message;
        $mail->setFrom('ilyaperun@seznam.cz', 'E-shop MOP'); //
        $mail->addTo($recipient);
        $mail->setSubject('Nová objednávka: ' . $item);

        $pdfPath = __DIR__ . '/../../www/podminky.pdf';
        if (file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $imagePath = __DIR__ . '/../../www/upload/avatars/default.png';
        $cid = null;
        
        if (file_exists($imagePath)) {
            $embedded = $mail->addEmbeddedFile($imagePath);
            $cid = trim($embedded->getHeader('Content-ID'), '<>');
        }

        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/email.latte');
        
        $template->name = $name;
        $template->item = $item;
        $template->note = $note;
        
        $template->cid = $cid; 

        $mail->setHtmlBody((string) $template);

        return $mail;
    }

    public function sendEmail(Message $mail): void
    {
        $this->mailer->send($mail);
    }
}