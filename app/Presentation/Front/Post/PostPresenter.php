<?php

declare(strict_types=1);

namespace App\Presentation\Front\Post;

use App\Model\PostFacade;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;

final class PostPresenter extends Presenter
{
    public function __construct(
        private readonly PostFacade $postFacade,
    ) {
    }

    // --- 1. ГРИД (DATAGRID) ---
    protected function createComponentGrid(string $name): Datagrid
    {
        $grid = new Datagrid($this, $name);

        $grid->setDataSource($this->postFacade->getAllPosts());

        $grid->addColumnNumber('id', 'ID')->setSortable();
        $grid->addColumnText('title', 'Заголовок')->setSortable();
        $grid->addColumnText('content', 'Содержание')
            ->setRenderer(function ($item) {
                return Strings::truncate((string) $item->content, 50);
            });

        // === ВОТ ТВОЕ ЗАДАНИЕ (ACTIONS) ===

        // 1. Детали (Detail)
        // Третий параметр 'detail' означает, что кнопка ведет на action renderDetail($id)
        $grid->addAction('detail', 'Detail', 'detail')
            ->setClass('btn btn-sm btn-info text-white'); // <-- Окрашиваем в голубой

        // 2. Редактирование (Edit)
        // Ведет на action renderEdit($id)
        $grid->addAction('edit', 'Upravit', 'edit')
            ->setClass('btn btn-sm btn-success'); // <-- Окрашиваем в зеленый

        // 3. Удаление (Delete)
        // 'delete!' с восклицательным знаком — это сигнал (signal)
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setClass('btn btn-sm btn-danger ajax') // <-- Окрашиваем в красный + AJAX
            ->setConfirmation(
                new \Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation('Опасно! Ты уверена, что хочешь удалить этот хвост?')
            );

        return $grid;
    }

    // --- 2. МЕТОДЫ ОТРИСОВКИ (Их не хватало!) ---

    // Открывается при нажатии кнопки "Detail"
    public function renderDetail(int $id): void
    {
        // Здесь мы должны найти пост по ID, но у нас в PostFacade пока нет простого getById.
        // Для примера просто передадим ID. В идеале нужно: $this->template->post = $this->postFacade->getById($id);
        $this->template->postId = $id;
    }

    // Открывается при нажатии кнопки "Edit"
    public function renderEdit(int $id): void
    {
        $this->template->postId = $id;
    }

    // --- 3. ОБРАБОТКА УДАЛЕНИЯ (HANDLE) ---
    public function handleDelete(int $id): void
    {
        // Удаляем пост через фасад
        $this->postFacade->deletePost($id);

        $this->flashMessage("Пост #$id был успешно изгнан в небытие.", 'success');

        // Если запрос пришел через AJAX (Naja.js), перерисовываем только таблицу
        if ($this->isAjax()) {
            $this['grid']->reload();
        } else {
            $this->redirect('this');
        }
    }
}