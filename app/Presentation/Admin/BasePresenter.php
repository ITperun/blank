<?php

declare(strict_types=1);

namespace App\Presentation\Admin;

use Nette;
use Nette\Application\UI\Form;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function createComponentSearchPostForm(): Form
	{
		$form = new Form;
		$form->addText('searchWord', 'Search posts:')
			->setRequired('Please enter search term.');
		$form->addSubmit('send', 'Search');

		$form->onSuccess[] = [$this, 'searchPostFormSucceeded'];

		return $form;
	}

	public function searchPostFormSucceeded(Form $form, \stdClass $data): void
	{
		$this->redirect(':Front:Post:results', ['searchWord' => $data->searchWord]);
	}
}
