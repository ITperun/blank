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
	#[Persistent]
	public string $backlink = '';

	/** @var string Ключ сайта из конфига */
	private string $recaptchaSiteKey;

	/** @var string Секретный ключ из конфига */
	private string $recaptchaSecretKey;

	public function __construct(
		private UserFacade $userFacade,
		private FormFactory $formFactory,
		private Nette\DI\Container $container, // Получаем доступ к параметрам
	) {
		parent::__construct();
		// Берем ключи из параметров common.neon
		$parameters = $this->container->getParameters();
		$this->recaptchaSiteKey = $parameters['recaptcha']['siteKey'];
		$this->recaptchaSecretKey = $parameters['recaptcha']['secretKey'];
	}

	/**
	 * Передаем ключ в шаблон перед его отрисовкой
	 */
	public function renderUp(): void
	{
		$this->template->siteKey = $this->recaptchaSiteKey;
	}

	protected function createComponentSignInForm(): Form
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Username:')->setRequired();
		$form->addPassword('password', 'Password:')->setRequired();
		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				$this->getUser()->login($data->username, $data->password);
				$this->restoreRequest($this->backlink);
				$this->redirect($this->getUser()->isInRole('admin') ? ':Admin:Dashboard:default' : ':Front:Home:default');
			} catch (Nette\Security\AuthenticationException) {
				$form->addError('The username or password you entered is incorrect.');
			}
		};
		return $form;
	}

	protected function createComponentSignUpForm(): Form
	{
		$form = $this->formFactory->create();
		$form->addText('firstname', 'First name:')->setRequired();
		$form->addText('lastname', 'Last name:')->setRequired();
		$form->addText('username', 'Pick a username:')->setRequired();
		$form->addEmail('email', 'Your e-mail:')->setRequired();
		$form->addPassword('password', 'Create a password:')
			->setRequired()
			->addRule($form::MinLength, null, $this->userFacade::PasswordMinLength);

		$form->addSubmit('send', 'Sign up');

		// Проверка капчи
		$form->onValidate[] = function (Form $form) {
			$presenter = $form->getPresenter();
			$response = $presenter->getHttpRequest()->getPost('g-recaptcha-response');

			if (!$this->verifyCaptcha((string) $response)) {
				$form->addError('Пожалуйста, подтвердите, что вы не робот.');
			}
		};

		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				$this->userFacade->add($data->username, $data->email, $data->password, null, $data->firstname, $data->lastname);
				$this->redirect(':Admin:Dashboard:');
			} catch (DuplicateNameException) {
				$form['username']->addError('Username is already taken.');
			}
		};
		return $form;
	}

	private function verifyCaptcha(string $response): bool
	{
		if (!$response) return false;

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$params = [
			'secret' => $this->recaptchaSecretKey,
			'response' => $response,
		];

		$options = ['http' => [
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($params),
		]];

		$result = @file_get_contents($url, false, stream_context_create($options));
		$data = json_decode((string)$result);
		return $data->success ?? false;
	}

	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->redirect('in');
	}
}