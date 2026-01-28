<?php

declare(strict_types=1);

namespace App\Presentation\Front\Home;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form; // <-- Важно! Не забудь эту строку
use App\MailSender\MailSender;

final class HomePresenter extends Presenter
{
    public function __construct(
        private MailSender $mailSender
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->title = 'Moje (DEV) MOP';
    }

    // --- НАЧАЛО НОВОГО КОДА ---

    // 1. Создаем форму
    protected function createComponentEmailForm(): Form
    {
        $form = new Form;

        $form->addEmail('recipient', 'Send to:')
            ->setRequired('Write Email.');

        $form->addText('subject', 'Tema:')
            ->setRequired('write teme.');

        // ВОТ ЗДЕСЬ ИЗМЕНЕНИЕ:
        $form->addTextArea('message', 'Content:')
            ->setRequired('Content.')
            ->setHtmlAttribute('rows', 5)
            ->setHtmlAttribute('class', 'tinymce'); // <--- Добавляем класс 'tinymce', чтобы включился редактор!

        $form->addSubmit('send', 'Send Email');

        $form->onSuccess[] = [$this, 'emailFormSucceeded'];

        return $form;
    }

    // 2. Обрабатываем отправку формы
    public function emailFormSucceeded(Form $form, \stdClass $values): void
    {
        // Здесь мы берем данные, которые ты ввела в форму ($values)
        // $values->recipient - это адрес из поля 'recipient'
        // $values->subject - это тема из поля 'subject'
        // $values->message - это текст из поля 'message'

        $mail = $this->mailSender->createNotificationEmail(
            $values->recipient, // Сюда подставится адрес из формы!
            "User",             // Имя (можно тоже добавить в форму, если нужно)
            $values->subject,   // Тема из формы
            $values->message    // Текст из формы
        );

        $this->mailSender->sendEmail($mail);

        $this->flashMessage('Email успешно отправлен!', 'success');
        $this->redirect('this');
    }

    // --- КОНЕЦ НОВОГО КОДА ---
    
    // Старый метод handleSendEmail удален, чтобы не мешался.
}