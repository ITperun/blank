<?php

declare(strict_types=1);

namespace App\Presentation\Front\Post;

use App\Model\PostFacade;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;

final class PostPresenter extends Presenter
{
    public function __construct(
        private readonly PostFacade $postFacade,
    ) {
    }

    protected function createComponentGrid(): Datagrid
    {
        $grid = new Datagrid();

        $grid->setDataSource($this->postFacade->getAllPosts());

        $grid->addColumnNumber('id', 'ID')
            ->setSortable();

        $grid->addColumnText('title', 'Title')
            ->setSortable();

        $grid->setDefaultPerPage(2);

        return $grid;
    }
}