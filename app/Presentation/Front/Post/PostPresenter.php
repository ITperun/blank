<?php

declare(strict_types=1);

namespace App\Presentation\Front\Post;

use App\Model\PostFacade;
use Contributte\Datagrid\Datagrid;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Nette\Utils\Paginator;
use Nette\Utils\Strings;

final class PostPresenter extends Presenter
{
    #[Persistent]
    public int $page = 1;

    public function __construct(
        private readonly PostFacade $postFacade,
    ) {
    }

    public function renderDefault(int $page = 1): void
{
    $posts = $this->postFacade->getAllPosts();

    $paginator = new Paginator();
    $paginator->setItemCount($posts->count('*'));
    $paginator->setItemsPerPage(10);
    
    $paginator->setPage($page); 

    $this->template->posts = $posts->limit($paginator->getLength(), $paginator->getOffset());
    $this->template->paginator = $paginator;
}

    protected function createComponentGrid(string $name): Datagrid
    {
        $grid = new Datagrid($this, $name);
        $grid->setDataSource($this->postFacade->getAllPosts());

        $grid->addColumnNumber('id', 'ID')->setSortable();
        $grid->addColumnText('title', 'Заголовок')->setSortable();
        $grid->addColumnText('content', 'Содержание')
            ->setRenderer(fn($item) => Strings::truncate((string) $item->content, 50));

        $grid->addAction('detail', 'Detail', 'detail')
            ->setClass('btn btn-sm btn-info text-white');

        $grid->addAction('edit', 'Upravit', 'edit')
            ->setClass('btn btn-sm btn-success');

        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setClass('btn btn-sm btn-danger ajax')
            ->setConfirmation(
                new \Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation('Опасно! Ты уверена, что хочешь удалить этот хвост?')
            );

        return $grid;
    }
    public function renderDetail(int $id): void
    {
        $post = $this->postFacade->getPostById($id);
        
        if (!$post) {
            $this->error('Post not found');
        }

        $this->template->post = $post;
    }
    public function renderEdit(int $id): void
    {
        $post = $this->postFacade->getPostById($id);
        
        if (!$post) {
            $this->error('Post not found');
        }

        $this['postForm']->setDefaults($post->toArray());
        $this->template->post = $post;
    }
    public function handleDelete(int $id): void
    {
        $this->postFacade->deletePost($id);
        $this->flashMessage("Пост #$id был успешно изгнан в небытие.", 'success');

        if ($this->isAjax()) {
            $this['grid']->reload();
        } else {
            $this->redirect('this');
        }
    }
    protected function createComponentPostForm(): \Nette\Application\UI\Form
    {
        $form = new \Nette\Application\UI\Form();
        $form->addText('title', 'Title:')->setRequired('Put the text!');
        $form->addTextArea('content', 'Content:')
            ->setRequired('Where?')
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('class', 'tinymce');

        $form->addSubmit('send', 'Save');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    public function postFormSucceeded(\Nette\Application\UI\Form $form, array $values): void
    {
        $postId = (int) $this->getParameter('id');
        $this->postFacade->editPost($postId, $values);
        $this->flashMessage('Success!', 'success');
        $this->redirect('default');
    }
}