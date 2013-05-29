<?php
/**
 * CreatorPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 14:10
 */

namespace ITILSimulator\Creator\Presenters;

use ITILSimulator\Base\BasePresenter;
use ITILSimulator\Runtime\Simulator\RoleEnum;

/**
 * Base presenter class for Creator Module.
 * @package ITILSimulator\Creator\Presenters
 */
abstract class CreatorPresenter extends BasePresenter
{
	public function startup() {
		parent::startup();

		$this->requireLogin();

		if (!$this->user->isInRole(RoleEnum::CREATOR) && !$this->user->isInRole(RoleEnum::ADMIN)) {
			// not authorized
			$this->forbidden();
		}

		$this->template->isAdmin = $this->user->isInRole(RoleEnum::ADMIN);
	}

	public function shutdown($response) {
		parent::shutdown($response);

		// commit changes to database
		$this->userService->commitChanges();
	}
}