<?php

declare(strict_types=1);

namespace App\Presentation\Admin\Search;

use App\Presentation\Admin\BasePresenter;
use Nette;
use Nette\Application\UI\Form;
use App\Model\UserFacade;

final class SearchPresenter extends BasePresenter
{
    public function __construct(
        private UserFacade $userFacade
    ) {}

    protected function createComponentSearchForm(): Form
    {
        $form = new Form;
        $form->addText('keyword', 'Search:')
            ->setRequired('Search.');

        $form->addSubmit('send', 'Search');
        $form->onSuccess[] = [$this, 'searchFormSucceeded'];
        return $form;
    }

    public function searchFormSucceeded(Form $form, array $values): void
    {
        $this->redirect('Search:results', ['keyword' => $values['keyword']]);
    }

    public function renderResults(string $keyword): void
    {
        $results = $this->userFacade->search($keyword);
        $this->template->keyword = $keyword;
        $this->template->results = $results;
    }
}
