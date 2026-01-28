<?php

declare(strict_types=1);

namespace App\Presentation\Admin\Dashboard;

use App\Presentation\Accessory\RequireLoggedUser;
use App\Presentation\Admin\BasePresenter;
use Nette;


/**
 * Presenter for the dashboard view.
 * Ensures the user is logged in before access.
 */
final class DashboardPresenter extends BasePresenter
{
	// Incorporates methods to check user login status
	use RequireLoggedUser;
}
