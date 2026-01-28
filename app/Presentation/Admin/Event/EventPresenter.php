<?php

declare(strict_types=1);

namespace App\Presentation\Admin\Event;

use Nette;
use Nette\Application\UI\Form;
use App\Model\EventFacade;

final class EventPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private EventFacade $eventFacade
    ) {}

    public function renderDefault(): void
    {
        $this->template->events = $this->eventFacade->getAllEvents();
    }

    public function renderEdit(?int $id = null): void
    {
        if ($id) {
            $event = $this->eventFacade->getEventById($id);
            if (!$event) {
                $this->error('Událost nebyla nalezena.');
            }
            $this['eventForm']->setDefaults($event->toArray());
        }
    }

    protected function createComponentEventForm(): Form
    {
        $form = new Form;
        $form->addText('title', 'Název události:')
            ->setRequired('Zadejte název.');
        $form->addText('location', 'Místo:')
            ->setRequired('Zadejte místo konání.');
        $form->addText('start_time', 'Začátek:')
            ->setHtmlType('datetime-local')
            ->setRequired();
        $form->addText('end_time', 'Konec:')
            ->setHtmlType('datetime-local');
        $form->addTextarea('description', 'Popis:')
            ->setRequired();
        $form->addHidden('id');

        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'eventFormSucceeded'];
        return $form;
    }

    public function eventFormSucceeded(Form $form, \stdClass $values): void
    {
        // Проверка дат
        $start = new \DateTime($values->start_time);
        $end = new \DateTime($values->end_time);

        if ($end && $start >= $end) {
            $form->addError('Datum začátku musí být před datem konce.');
            return;
        }

        if ($values->id) {
            $this->eventFacade->updateEvent((int) $values->id, (array) $values);
            $this->flashMessage('Událost byla upravena.', 'success');
        } else {
            $this->eventFacade->createEvent((array) $values);
            $this->flashMessage('Událost byla vytvořena.', 'success');
        }
        $this->redirect('default');
    }

    public function handleDelete(int $id): void
    {
        $this->eventFacade->deleteEvent($id);
        $this->flashMessage('Událost byla smazána.', 'info');
        $this->redirect('this');
    }
}
