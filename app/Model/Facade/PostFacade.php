<?php

declare(strict_types=1);

namespace App\Model\Facade;

use Nette\Database\Explorer;

final class PostFacade
{
    public function __construct(
        private readonly Explorer $database,
    ) {
    }

    public function getAllPosts()
    {
        return $this->database->table('posts');
    }
}
