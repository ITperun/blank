<?php

declare(strict_types=1);

namespace App\Presentation\Front\Home;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use App\MailSender\MailSender;
use App\Model\LocationFacade;

final class HomePresenter extends Presenter
{
    public function __construct(
        private MailSender $mailSender,
        private LocationFacade $locationFacade
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->title = 'Moje (DEV) MOP';
    }

    protected function createComponentEmailForm(): Form
    {
        $form = new Form;

        $form->addEmail('recipient', 'Send to:')
            ->setRequired('Write Email.');

        $form->addText('subject', 'Tema:')
            ->setRequired('write teme.');

        $form->addTextArea('message', 'Content:')
            ->setRequired('Content.')
            ->setHtmlAttribute('rows', 5)
            ->setHtmlAttribute('class', 'tinymce');

        $form->addSubmit('send', 'Send Email');

        $form->onSuccess[] = [$this, 'emailFormSucceeded'];

        return $form;
    }

    public function emailFormSucceeded(Form $form, \stdClass $values): void
    {

        $mail = $this->mailSender->createNotificationEmail(
            $values->recipient,
            "User",
            $values->subject,
            $values->message
        );

        $this->mailSender->sendEmail($mail);

        $this->flashMessage('Email успешно отправлен!', 'success');
        $this->redirect('this');
    }

    /** @var int|null @persistent */
    public $countryId = null;

    protected function createComponentDependentForm(): Form
    {
        $form = new Form;

        $country = $form->addSelect('country', 'Země:', $this->locationFacade->getCountries())
            ->setPrompt('Vyberte zemi...')
            ->setHtmlAttribute('data-ajax-change')
            ->setRequired('Musíte vybrat zemi.');

        if ($this->countryId) {
            $country->setDefaultValue($this->countryId);
        }

        $cities = [];
        if ($this->countryId) {
            $cities = $this->locationFacade->getCities((int)$this->countryId);
        }

        $form->addSelect('city', 'Město:', $cities)
            ->setPrompt($this->countryId ? 'Vyberte město...' : 'Nejprve vyberte zemi')
            ->setDisabled(!$this->countryId);

        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = [$this, 'dependentFormSucceeded'];

        return $form;
    }
    public function handleUpdateCity($val): void
    {
        $this->countryId = $val ? (int)$val : null;
        if ($this->isAjax()) {
            $this->redrawControl('formSnippet');
        }
    }

    public function dependentFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->flashMessage("Vybrala jsi zemi ID: $values->country a město ID: $values->city", 'success');
        $this->redirect('this');
    }

}