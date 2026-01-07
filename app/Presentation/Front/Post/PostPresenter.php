<?php

declare(strict_types=1);

namespace App\Presentation\Front\Post;

use App\Model\Facade\PostFacade; // Added correct namespace
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;

final class PostPresenter extends Presenter
{
    public function __construct(
        private readonly PostFacade $postFacade, // Now uses correct class
    ) {
    }

    protected function createComponentGrid(): Datagrid
    {
        $grid = new Datagrid();

        // Use facade for data access (requirement a)
        $grid->setDataSource($this->postFacade->getAllPosts());

        // Add numeric column (requirement b)
        $grid->addColumnNumber('id', 'ID')
            ->setSortable(); // Sortable (part of requirement e)

        // Add text column, make sortable (requirement e)
        $grid->addColumnText('title', 'Title')
            ->setSortable();

        // Set default pagination to 2 (requirement d)
        $grid->setDefaultPerPage(2);

        return $grid;
    }
}