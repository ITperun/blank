<?php

declare(strict_types=1);

namespace App\Presentation\Front\Home;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use App\Model\EventFacade;

final class HomePresenter extends Presenter
{
    private EventFacade $eventFacade;

    public function __construct(EventFacade $eventFacade)
    {
        parent::__construct();
        $this->eventFacade = $eventFacade;
    }

    public function renderDefault(int $page = 1, string $keyword = ''): void
    {
        $itemsPerPage = 9;
        
        // Получаем события с фильтром
        $eventsCount = $this->eventFacade->countUpcomingEvents($keyword);
        $offset = ($page - 1) * $itemsPerPage;
        $events = $this->eventFacade->getUpcomingEventsPaginated($itemsPerPage, $offset, $keyword);
        
        // Передаем данные в шаблон
        $this->template->title = 'Klub deskových her';
        $this->template->events = $events;
        $this->template->currentPage = $page;
        $this->template->totalPages = ceil($eventsCount / $itemsPerPage);
        $this->template->eventsCount = $eventsCount;
        $this->template->keyword = $keyword;

    }

    protected function createComponentSearchForm(): Form
    {
        $form = new Form;
        $form->addText('keyword', 'Hledat:')
            ->setHtmlAttribute('placeholder', 'Zadejte název, místo nebo popis...');
        
        $form->addSubmit('search', 'Hledat')
            ->setHtmlAttribute('class', 'btn btn-primary');
            
        $form->onSuccess[] = [$this, 'searchFormSucceeded'];
        
        // Заполняем форму текущим значением поиска
        if ($this->getParameter('keyword')) {
            $form->setDefaults(['keyword' => $this->getParameter('keyword')]);
        }
        
        return $form;
    }

    public function searchFormSucceeded(Form $form, array $values): void
    {
        $keyword = trim($values['keyword']);
        $this->redirect('default', ['keyword' => $keyword, 'page' => 1]);
    }
}