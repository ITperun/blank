<?php

declare(strict_types=1);

namespace App\Presentation\Front\Post;

use App\Model\PostFacade;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings; // Не забудь эту магию для работы со строками!

final class PostPresenter extends Presenter
{
    public function __construct(
        private readonly PostFacade $postFacade,
    ) {
    }

    protected function createComponentGrid(string $name): Datagrid
    {
        $grid = new Datagrid($this, $name);

        // Источник данных
        $grid->setDataSource($this->postFacade->getAllPosts());

        // Столбцы
        $grid->addColumnNumber('id', 'ID')
            ->setSortable();

        $grid->addColumnText('title', 'Заголовок')
            ->setSortable();

        // ИСПРАВЛЕНИЕ: Используем setRenderer вместо setTruncate
        $grid->addColumnText('content', 'Содержание')
            ->setRenderer(function ($item) {
                return Strings::truncate((string) $item->content, 50);
            });

        // Действия (Actions)

        // 1. Детали
        $grid->addAction('detail', 'Detail', 'detail')
            ->setClass('btn btn-sm btn-info text-white');

        // 2. Редактирование
        $grid->addAction('edit', 'Upravit', 'edit')
            ->setClass('btn btn-sm btn-success');

        // 3. Удаление
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setClass('btn btn-sm btn-danger ajax')
            ->setConfirmation(
                // Если вдруг класс не найдется, попробуй заменить Contributte на Ublaboo в начале пути
                new \Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation('Опасно! Ты уверена, что хочешь удалить этот хвост?')
            );

        // Пагинация
        $grid->setDefaultPerPage(2);

        return $grid;
    }

    // Обработчик удаления
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
}