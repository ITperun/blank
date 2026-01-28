<?php

namespace App\Presentation\Front\Event;

use Nette;
use Nette\Forms\Form;
use App\Model\EventFacade;

final class EventPresenter extends Nette\Application\UI\Presenter
{
    private EventFacade $eventFacade;

    public function __construct(EventFacade $eventFacade)
    {
        parent::__construct();
        $this->eventFacade = $eventFacade;
    }

    public function renderCreate(): void
    {
        if (
            !$this->user->isLoggedIn()
            || !in_array($this->user->identity->role, ['admin', 'creator', 'organizer', 'moderator'])
        ) {
            $this->error('Nemáte oprávnění pro vytvoření události.', Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }

    protected function createComponentCreateEventForm(): Form
    {
        $form = new Form;

        $form->addText('name', 'Název události:')
            ->setRequired('Zadejte název události.');

        $form->addTextArea('description', 'Popis:')
            ->setHtmlAttribute('rows', 4);

        $form->addText('location', 'Místo konání:')
            ->setRequired('Zadejte místo konání.');

        $form->addText('date', 'Datum a čas:')
            ->setHtmlType('datetime-local')
            ->setRequired('Zadejte datum a čas.');

        $form->addSubmit('save', 'Vytvořit událost')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'createEventFormSucceeded'];
        return $form;
    }

    public function createEventFormSucceeded(Form $form, \stdClass $values): void
    {
        $user = $this->user->identity;
        if (!in_array($user->role, ['admin', 'creator', 'organizer', 'moderator'])) {
            $this->flashMessage('Nemáte oprávnění k vytvoření události.', 'danger');
            $this->redirect('Homepage:default');
            return;
        }

        $eventData = [
            'name' => $values->name,
            'description' => $values->description,
            'location' => $values->location,
            'date' => $values->date,
            'organizer_id' => $user->id,
            'status' => 'pending',
        ];

        $this->eventFacade->createEvent($eventData);
        $this->flashMessage('Událost byla vytvořena a čeká na schválení moderátorem.', 'success');
        $this->redirect('Homepage:default');
    }

    public function renderDefault(): void
    {
        $this->template->events = $this->eventFacade->getUpcomingEvents();
    }

    public function renderDetail(int $id): void
    {
        $event = $this->eventFacade->getEventById($id);
        if (!$event) {
            $this->error('Událost nebyla nalezena');
        }

        $userId = $this->user->isLoggedIn() ? $this->user->identity->id : null;
        $userRole = $this->user->isLoggedIn() ? $this->user->identity->role : null;
        $isJoined = $userId ? $this->eventFacade->isUserJoined($id, $userId) : false;

        $isAdmin = $userRole === 'admin';
        $isModerator = $userRole === 'moderator';
        $isOrganizer = $userId && $event->organizer_id == $userId;
        $canViewChat = $isJoined || $isAdmin || $isOrganizer || $isModerator;
        $canChat = $isJoined || $isAdmin || $isModerator;

        $chatMessages = $canViewChat ? $this->eventFacade->getChatMessages($id) : [];

        $this->template->event = $event;
        $this->template->isJoined = $isJoined;
        $this->template->canViewChat = $canViewChat;
        $this->template->canChat = $canChat;
        $this->template->chatMessages = $chatMessages;
        $this->template->isAdmin = $isAdmin;
        $this->template->isModerator = $isModerator;
    }

    // Форма для отправки сообщения
    protected function createComponentChatForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;
        $form->addText('content', 'Zpráva:')
            ->setRequired('Napište zprávu.');
        $form->addSubmit('send', 'Odeslat');
        $form->onSuccess[] = [$this, 'chatFormSucceeded'];
        return $form;
    }

    public function chatFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Pro odeslání zprávy se musíte přihlásit.', 'danger');
            $this->redirect('this');
            return;
        }
        $eventId = $this->getParameter('id');
        $userId = $this->user->identity->id;
        $userRole = $this->user->identity->role ?? null;

        // Админ и модератор могут писать всегда, остальные — только участники
        if (!in_array($userRole, ['admin', 'moderator']) && !$this->eventFacade->isUserJoined($eventId, $userId)) {
            $this->flashMessage('Pouze účastníci mohou psát do chatu.', 'danger');
            $this->redirect('this');
            return;
        }

        $this->eventFacade->addChatMessage($eventId, $userId, $values->content);

        $this->flashMessage('Zpráva byla odeslána.', 'success');
        $this->redirect('this');
    }

    public function joinEvent(int $eventId, int $userId): void
    {
        $this->db->table('event_participants')->insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'joined_at' => new \DateTime(),
        ]);
    }

    public function handleJoin(int $id): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Pro připojení k události se musíte přihlásit.', 'warning');
            $this->redirect(':Front:Sign:in');
            return;
        }

        $userId = $this->user->id;

        if (!$this->eventFacade->isUserJoined($id, $userId)) {
            $this->eventFacade->joinEvent($id, $userId);
            $this->flashMessage('Byla jste přihlášena k události.', 'success');
        } else {
            $this->flashMessage('Už jste přihlášena k této události.', 'info');
        }

        $this->redirect('this'); // Перезагружает текущую страницу detail
    }

    public function handleLeave(int $id): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Pro odhlášení se musíte přihlásit.', 'warning');
            $this->redirect(':Front:Sign:in');
            return;
        }

        $userId = $this->user->id;

        if ($this->eventFacade->isUserJoined($id, $userId)) {
            $this->eventFacade->leaveEvent($id, $userId);
            $this->flashMessage('Byl jste odhlášen z události.', 'info');
        } else {
            $this->flashMessage('Nejste přihlášen k této události.', 'warning');
        }

        $this->redirect('this');
    }

    public function handleDeleteMessage(int $messageId): void
    {
        $role = $this->user->isLoggedIn() ? $this->user->identity->role : null;
        if (!$this->user->isLoggedIn() || !in_array($role, ['admin', 'moderator'])) {
            $this->flashMessage('Pouze administrátor nebo moderátor může mazat zprávy.', 'danger');
            $this->redirect('this');
            return;
        }
        $this->eventFacade->deleteChatMessage($messageId);
        $this->flashMessage('Zpráva byla smazána.', 'info');
        $this->redirect('this');
    }

    public function handleDeleteEvent(int $id): void
    {
        $role = $this->user->isLoggedIn() ? $this->user->identity->role : null;
        if (!$this->user->isLoggedIn() || !in_array($role, ['admin', 'moderator'])) {
            $this->flashMessage('Pouze administrátor nebo moderátor může mazat události.', 'danger');
            $this->redirect('this');
            return;
        }
        $this->eventFacade->deleteAllChatMessagesForEvent($id);
        $this->eventFacade->deleteEvent($id);
        $this->flashMessage('Událost a její chat byly smazány.', 'info');
        $this->redirect('Homepage:default');
    }
}
