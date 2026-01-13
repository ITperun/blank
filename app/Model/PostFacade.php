<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\Table\Selection;

final class PostFacade
{
	public function __construct(
		private Nette\Database\Explorer $database,
	) {
	}

    // --- ВОТ ЭТО НУЖНО ДОБАВИТЬ ДЛЯ ЗАДАНИЯ ---
    // Возвращаем Selection, не делаем fetchAll()!
    // DataGrid сам "дернет" данные, когда ему нужно будет отрисовать страницу 1 или 2.
	public function getAllPosts(): Selection
	{
		return $this->database->table('posts');
	}

    // --- А ЭТО МОЖЕШЬ ОСТАВИТЬ (но пока не используем в гриде) ---
	public function getArticlesBySearchWord(string $searchWord): array
	{
		$keyword = "%$searchWord%";
		return $this->database->table('posts')
            // Внимание: в твоей таблице поле называется content, а не text
			->where('content LIKE ? OR title LIKE ?', $keyword, $keyword)
			->fetchAll();
	}

	public function deletePost(int $id): void
{
    $this->database->table('posts')->where('id', $id)->delete();
}
}