<?php

declare(strict_types=1);

namespace App\Presentation\Admin\User;

use App\Presentation\Admin\BasePresenter;
use Nette;
use App\Model\UserFacade;

final class UserPresenter extends BasePresenter
{
    public function __construct(
        private UserFacade $userFacade
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->userData = $this->userFacade->getAllUsers();
    }

    public function renderDetail(int $id): void
    {
        $user = $this->userFacade->getUserById($id);
        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }
        $this->template->userData = $user;
    }

    public function renderEdit(?int $id = null): void
    {
        $targetId = $this->checkEditPermissions($id);
        
        $user = $this->userFacade->getUserById($targetId);
        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }
        $this->template->userData = $user;
    }

public function createComponentEditForm(): Nette\Application\UI\Form
{
    $idParam = (int) $this->getParameter('id');
    $targetId = $this->checkEditPermissions($idParam);

    $form = new Nette\Application\UI\Form;

    $form->addText('username', 'Užívateľské jméno')->setRequired();
    $form->addText('email', 'Email')->setRequired();
    $form->addPassword('password', 'Heslo (ponechte prázdné, pokud nechcete měnit)');

    $form->addUpload('image', 'Soubor (avatar)')
        ->addRule(Nette\Forms\Form::IMAGE, 'Soubor musí být JPEG, PNG nebo GIF')
        ->setRequired(false);

    if ($this->user->isInRole('admin')) {
        $select = $form->addSelect('role', 'Role', [
            'admin' => 'Admin',
            'user' => 'User',
        ]);
    }

    $existingUser = $this->userFacade->getUserById($targetId);
    if ($existingUser) {
        $formData = $existingUser->toArray();
        unset($formData['password']);
        $form->setDefaults($formData);

        if ($this->user->isInRole('admin') && isset($select)) {
            $select->setDefaultValue($formData['role']);
        }
    }

    $form->addSubmit('send', 'Uložit');
    $form->onSuccess[] = [$this, 'editFormSucceeded'];

    return $form;
}

public function editFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void
{
    $valuesArray = (array) $values;
    $userId = (int) $this->getParameter('id');
    $userData = $this->userFacade->getUserById($userId);

    // Загрузка нового изображения
    if (!empty($valuesArray['image']) && $valuesArray['image']->getSize() > 0) {
        // Если уже был аватар — удалить старый файл
        if ($userData && !empty($userData['image']) && file_exists(__DIR__ . '/../../../../www/' . $userData['image'])) {
            unlink(__DIR__ . '/../../../../www/' . $userData['image']);
        }

        $uploadDir = realpath(__DIR__ . '/../../../../') . '/www/upload/avatars/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $sanitizedName = $valuesArray['image']->getSanitizedName();
        $uniqueName = uniqid() . "_" . $sanitizedName;
        $path = "upload/avatars/" . $uniqueName;

        $valuesArray['image']->move($uploadDir . $uniqueName);
        $valuesArray['image'] = $path;
    } else {
        unset($valuesArray['image']);
    }

    // Изменение пароля
    if (!empty($values->password)) {
        $this->userFacade->changePassword($userId, $values->password);
    }
    unset($valuesArray['password']);

    $this->userFacade->edit($userId, $valuesArray);

    // Обновление identity для текущего пользователя чтобы небыло проблем с отображением старого аватара из кукки если он был изменен на новый
    if ($this->user->getId() === $userId) {
        $identity = $this->user->getIdentity();
        foreach ($valuesArray as $key => $value) {
            $identity->$key = $value;
        }
        $this->user->login($identity);
    }

    $this->flashMessage('Profil byl úspěšně upraven.', 'success');
    $this->redirect('edit', ['id' => $userId]);
}


    public function handleDelete(int $userId): void
{
    if ($this->user->getId() === $userId) {
        $this->flashMessage('Nemůžete smazat sám sebe.', 'warning');
        $this->redirect('this');
    }

    if (!$this->user->isInRole('admin')) {
        $this->flashMessage('Nemáte oprávnění mazat uživatele.', 'danger');
        $this->redirect('this');
    }

    $this->userFacade->delete($userId);
    $this->flashMessage('Uživatel byl úspěšně smazán.', 'success');
    $this->redirect('User:default');
}

public function handleDeleteImage(int $userId): void
{
    $userData = $this->userFacade->getUserById($userId);

    if ($userData && !empty($userData['image']) && file_exists(__DIR__ . '/../../../../www/' . $userData['image'])) {
        unlink(__DIR__ . '/../../../../www/' . $userData['image']);
    }

    $this->userFacade->edit($userId, ['image' => null]);

    $this->flashMessage('Obrázek k uživateli byl smazán', 'success');

    if ($this->isAjax()) {
        $this->redrawControl('userImage'); // <- указываем snippet, который нужно обновить
    } else {
        $this->redirect('this');
    }
}



    private function checkEditPermissions(?int $id = null): int
    {
        if ($this->user->isInRole('admin')) {
            return $id ?? $this->user->getId();
        } else {
            // Обычный пользователь может редактировать только свой профиль
            if ($id !== null && $id !== $this->user->getId()) {
                $this->flashMessage('Nemáte oprávnění upravovat cizí profil.', 'danger');
                $this->redirect('edit', ['id' => $this->user->getId()]);
            }
            return $this->user->getId();
        }
    }
}