<?php

declare(strict_types=1);

namespace App\Presentation\Front\Home;

use Nette\Application\UI\Presenter;
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
    public function handleSendEmail(): void
    {
        $mail = $this->mailSender->createNotificationEmail(
            "zakaznik@seznam.cz",
            "Martin Kokes", 
            "Autíčko Burago Hyundai IONIQ5", 
            "Prosím o zelenou barvu"
        );

        $this->mailSender->sendEmail($mail);

        $this->flashMessage('Email byl úspěšně odeslán!', 'success');
        $this->redirect('this');
    }
}