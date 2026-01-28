<?php

declare(strict_types=1);

namespace App\Presentation\Front\Sign;

use App\Model\DuplicateNameException;
use App\Model\UserFacade;
use App\Presentation\Accessory\FormFactory;
use Nette;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;


/**
 * Presenter for sign-in and sign-up actions.
 */
final class SignPresenter extends Nette\Application\UI\Presenter
{
	/**
	 * Stores the previous page hash to redirect back after successful login.
	 */
	#[Persistent]
	public string $backlink = '';


	// Dependency injection of form factory and user management facade
	public function __construct(
		private UserFacade $userFacade,
		private FormFactory $formFactory,
	) {
	}


	/**
	 * Create a sign-in form with fields for username and password.
	 * On successful submission, the user is redirected to the dashboard or back to the previous page.
	 */
	protected function createComponentSignInForm(): Form
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.')
			->setHtmlAttribute('autocomplete', 'current-password');

		$form->addSubmit('send', 'Sign in');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				// Attempt to login user
				$this->getUser()->login($data->username, $data->password);
				$this->restoreRequest($this->backlink);
				$this->redirect(':Admin:Dashboard:');
			} catch (Nette\Security\AuthenticationException) {
				$form->addError('The username or password you entered is incorrect.');
			}
		};

		return $form;
	}


	/**
	 * Create a sign-up form with fields for username, email, and password.
	 * On successful submission, the user is redirected to the dashboard.
	 */
	protected function createComponentSignUpForm(): Form
{
    $form = $this->formFactory->create();

    $form->addText('firstname', 'First name:')
        ->setRequired('Please enter your first name.');

    $form->addText('lastname', 'Last name:')
        ->setRequired('Please enter your last name.');

    $form->addText('username', 'Pick a username:')
        ->setRequired('Please pick a username.')
        ->addRule($form::MIN_LENGTH, 'Username must be at least 3 characters.', 3);

    $form->addText('birthdate', 'Date of birth:')
        ->setHtmlType('date')
        ->setRequired('Please enter your date of birth.');

    $form->addEmail('email', 'Your e-mail:')
        ->setRequired('Please enter your e-mail.');

    $form->addPassword('password', 'Create a password:')
        ->setOption('description', 'at least 5 characters')
        ->setRequired('Please create a password.')
        ->addRule($form::MIN_LENGTH, null, 5)
        ->setHtmlAttribute('autocomplete', 'new-password');

    $form->addSubmit('send', 'Sign up');

    $form->onSuccess[] = function (Form $form, \stdClass $data): void {
        // Проверка уникальности ника и почты
        if ($this->userFacade->getByUsername($data->username)) {
            $form['username']->addError('Username is already taken.');
            return;
        }
        if ($this->userFacade->getByEmail($data->email)) {
            $form['email']->addError('Email is already registered.');
            return;
        }

        try {
            $this->userFacade->add(
                $data->username,
                $data->email,
                $data->password,
                role: 'user',
                firstname: $data->firstname,
                lastname: $data->lastname,
                birthdate: $data->birthdate
            );
            $this->redirect(':Admin:Dashboard:');
        } catch (DuplicateNameException) {
            $form['username']->addError('Username is already taken.');
        }
    };

    return $form;
}



	/**
	 * Logs out the currently authenticated user.
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
	}
}
