<?php
/**
 * UsersPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 12.5.13 14:14
 */

namespace CreatorModule;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Base\ITILConfigurator;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\Simulator\Role;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

/**
 * Presenter for managing users
 * @package CreatorModule
 */
class UsersPresenter extends CreatorPresenter
{
	#region "Properties"

	/** @var User */
	protected $selectedUser;

	#endregion

	#region "Lifecycle methods"

	public function startup() {
		parent::startup();

		if (!$this->user->isInRole(RoleEnum::ADMIN)) {
			$this->forbidden();
		}
	}

	#endregion

	#region "Default and edit"

	/**
	 * List of all users in the system
	 */
	public function renderDefault() {
		$this->template->users = $this->userService->getUsers();
	}

	/**
	 * Edit selected user
	 * @param int $id User ID
	 */
	public function actionEdit($id) {
		$this->selectedUser = $this->userService->getUser($id);

		FormHelper::setDefaultValues($this['userForm'], $this->selectedUser);

		$roles = array_keys($this->selectedUser->getRolesList());

		$this['userForm']->setDefaults(array('roles' => $roles, 'isAnonymous' => $this->selectedUser->isAnonymous() ? '1' : '0'));
	}

	/**
	 * Add new user
	 */
	public function actionNew() {
		$this->selectedUser = new User();
	}

	/**
	 * Delete an existing user
	 * @param int $id User ID
	 */
	public function handleDelete($id) {
		$this->selectedUser = $this->userService->getUser($id);
		if ($this->selectedUser) {
			$this->userService->deleteUser($this->selectedUser);

			$this->flashInfoMessage('User %s was deleted.', $this->selectedUser->getName());
		}

		$this->redirect('default');
	}

	#endregion

	#region "Components"

	/**
	 * Create user form component
	 * @return Form
	 */
	protected function createComponentUserForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'First and last name:')
			->addRule(Form::FILLED, 'Name is required.')
			->addRule(Form::MAX_LENGTH, 'Maximum length is %s', 250);

		$form->addText('email', 'E-mail:')
			->addRule(Form::FILLED, 'E-mail is required.')
			->addRule(Form::EMAIL);

		$form->addPassword('password', 'New password:');
		$form->addRadioList('isAnonymous', 'Is registered:', array('0' => 'yes', '1' => 'no'));

		$form->addMultiSelect('roles', 'Roles:', $this->getRoles(true));

		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->onUserFormSuccess;
		$form->onValidate[] = $this->onUserFormValidate;

		return $form;
	}

	/**
	 * Validate user form
	 * @param Form $form
	 */
	public function onUserFormValidate(Form $form) {
		$values = $form->getValues();
		if (!$this->userService->isEmailUnique($values['email'], $this->selectedUser->getId())) {
			$form->addError('User with selected e-mail address already exists.');
		}
	}

	/**
	 * Save user form
	 * @param Form $form
	 */
	public function onUserFormSuccess(Form $form) {
		if ($this->selectedUser) {
			$values = (array)$form->getValues();

			$userValues = $values;
			unset($userValues['roles'], $userValues['password']);

			// update standard values
			FormHelper::updateValues($this->selectedUser, $userValues);

			// update password
			if ($values['password']) {
				$this->selectedUser->setPassword($values['password'], $this->itilConfigurator->getHashMethod());
			}

			// update roles
			$roleObjects = $this->getRoles(false);
			$this->selectedUser->clearRoles();

			foreach($values['roles'] as $role) {
				if (isset($roleObjects[$role]))
					$this->selectedUser->addRole($roleObjects[$role]);
			}

			$this->userService->updateUser($this->selectedUser);

			$this->flashInfoMessage('User %s was saved.', $this->selectedUser->getName());
		}

		$this->redirect('default');
	}

	#endregion

	#region "Helpers"

	/**
	 * Load available roles in the system.
	 * @param bool $asKeyValueArray TRUE to return roles as associative array (key = ID, value = role name).
	 * @return array|Role[]
	 */
	protected function getRoles($asKeyValueArray) {
		$roles = $this->userService->getRoles();

		$result = array();
		foreach ($roles as $role) {
			$result[$role->getId()] = $asKeyValueArray ? $role->getName() : $role;
		}

		return $result;
	}

	#endregion
}