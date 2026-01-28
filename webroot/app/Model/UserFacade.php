<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Passwords;


/**
 * Manages user-related operations such as authentication and adding new users.
 */
final class UserFacade implements Nette\Security\Authenticator
{
	// Minimum password length requirement for users
	public const PasswordMinLength = 7;

	// Database table and column names
	private const
    	TableName = 'users',
    	ColumnId = 'id',
    	ColumnName = 'username',
    	ColumnFirstname = 'firstname',
    	ColumnLastname = 'lastname',
    	ColumnPasswordHash = 'password',
    	ColumnEmail = 'email',
    	ColumnRoleId = 'role_id',
    	ColumnImage = 'image',
    	ColumnBirthdate = 'birthdate';



	// Dependency injection of database explorer and password utilities
	public function __construct(
		private Nette\Database\Explorer $database,
		private Passwords $passwords,
	) {
	}


	/**
	 * Authenticate a user based on provided credentials.
	 * Throws an AuthenticationException if authentication fails.
	 */
	public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
	{
		$user = $this->database->table(self::TableName)
			->where(self::ColumnName, $username)
			->fetch();

		if (!$user) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IdentityNotFound);

		} elseif (!$this->verifyPassword($user, $password)) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::InvalidCredential);
		}

		return $this->createIdentity($user);
	}


	public function verifyPassword(ActiveRow $user, string $password): bool
	{
		if (!$this->passwords->verify($password, $user[self::ColumnPasswordHash])) {
			return false;
		}

		if ($this->passwords->needsRehash($user[self::ColumnPasswordHash])) {
			$user->update([
				self::ColumnPasswordHash => $this->passwords->hash($password),
			]);
		}

		return true;
	}


	public function createIdentity(ActiveRow $user): Nette\Security\IIdentity
	{
		$arr = $user->toArray();
		unset($arr[self::ColumnPasswordHash]);

		// Получаем имя роли по role_id
		$roleName = $this->getRoleNameById($user[self::ColumnRoleId]);
		$arr['role'] = $roleName;

		return new Nette\Security\SimpleIdentity($user[self::ColumnId], $roleName, $arr);
	}

	public function getRoleNameById(int $roleId): string
	{
		$row = $this->database->table('roles')->get($roleId);
		return $row ? $row->name : 'user';
	}

	public function getRoleIdByName(string $roleName): ?int
	{
		$row = $this->database->table('roles')->where('name', $roleName)->fetch();
		return $row ? $row->id : null;
	}

	public function getAllRoles(): array
	{
		return $this->database->table('roles')->fetchPairs('id', 'name');
	}


	/**
	 * Add a new user to the database.
	 * Throws a DuplicateNameException if the username is already taken.
	 */
public function add(
    string $username,
    string $email,
    string $password,
    ?string $role = null,
    ?string $firstname = null,
    ?string $lastname = null,
    ?string $birthdate = null
): ActiveRow
{
    Nette\Utils\Validators::assert($email, 'email');
    $roleName = $role ?? 'user';
    $roleId = $this->getRoleIdByName($roleName) ?? $this->getRoleIdByName('user');

    try {
        return $this->database->table(self::TableName)->insert([
            self::ColumnName => $username,
            self::ColumnFirstname => $firstname,
            self::ColumnLastname => $lastname,
            self::ColumnPasswordHash => $this->passwords->hash($password),
            self::ColumnEmail => $email,
            self::ColumnRoleId => $roleId,
            self::ColumnBirthdate => $birthdate,
        ]);
    } catch (Nette\Database\UniqueConstraintViolationException $e) {
        throw new DuplicateNameException;
    }
}

public function getByEmail(string $email)
{
    return $this->database->table(self::TableName)
        ->where(self::ColumnEmail, $email)
        ->fetch();
}



	public function getAllUsers(): Nette\Database\Table\Selection
{
	return $this->database->table(self::TableName);
}

public function getUserById(int $id): ?ActiveRow
{
	return $this->database->table(self::TableName)
		->where(self::ColumnId, $id)
		->fetch();
}

public function edit(int $userId, array $data): void
{
    $this->getUserById($userId)->update($data);
}

public function changePassword(int $userId, string $newPassword): void
{
    $this->getUserById($userId)->update([
    self::ColumnPasswordHash => $this->passwords->hash($newPassword)
]);

}

public function getByUsername(string $username)
{
    return $this->database->table(self::TableName)
        ->where(self::ColumnName, $username)
        ->fetch();
}

public function delete(int $userId): void
{
    $user = $this->getUserById($userId);
    if ($user) {
        $user->delete();
    }
}

public function search(string $keyword)
{
    return $this->database->table('users')
        ->select('users.*, roles.name AS role')
        ->whereOr([
            self::ColumnName . ' LIKE ?' => "%$keyword%",
            self::ColumnEmail . ' LIKE ?' => "%$keyword%",
        ])
        ->order('users.id')
        ->fetchAll();
}





}


/**
 * Custom exception for duplicate usernames.
 */
class DuplicateNameException extends \Exception
{
}
