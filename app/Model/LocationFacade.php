<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

final class LocationFacade
{
	public function __construct(
		private Nette\Database\Explorer $database,
	) {
	}

	public function getCountries(): array
	{
		return $this->database->table('countries')->fetchPairs('id', 'name');
	}

	public function getCities(int $countryId): array
	{
		return $this->database->table('cities')
            ->where('country_id', $countryId)
            ->fetchPairs('id', 'name');
	}
}