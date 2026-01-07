<?php

declare(strict_types=1);

namespace App\Presentation\Front\Home;
use Nette\Application\UI\Presenter;


final class HomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Moje (DEV) MOP';
    }
}
