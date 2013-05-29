<?php

namespace TrainerModule;

use ITILSimulator\Base\BasePresenter;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\Strings;

/**
 * User presenter
 */
class UserPresenter extends BasePresenter
{
	#region "Lifecycle methods"

	public function startup() {
		parent::startup();

		if (!$this->user->isLoggedIn() && $this->action != 'login') {
			$this->redirect('login');
		}
	}

	public function actionDefault() {
		$this->redirect('myProfile');
	}

	#endregion

	#region "Login form"

	/**
	 * Create sign-in form component
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);

		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.')
			->getLabelPrototype()->addClass('required');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.')
			->getLabelPrototype()->addClass('required');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign me in');

		$form->onSuccess[] = $this->onSignInFormSuccess;

		return $form;
	}

	/**
	 * Save sign in form (validate credentials and log in user)
	 * @param Form $form
	 */
	public function onSignInFormSuccess(Form $form)
	{
		$values = $form->getValues();

		if ($values->remember) {
			$this->getUser()->setExpiration('+ 14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('+ 20 minutes', TRUE);
		}

		try {
			$this->user->login($values->username, $values->password);

			$user = $this->user->getId();
			$user->setDateLastLogin(new \DateTime());
			$this->userService->updateUser($user);

		} catch (AuthenticationException $e) {
			$form->addError($this->translator->translate($e->getMessage()));
			if ($this->isAjax())
				$this->invalidateControl('signInForm');

			return;
		}

		$this->redirect('default:default');
	}

	#endregion

	#region "Registration"

	/**
	 * Create registration form component
	 * @return Form
	 */
	public function createComponentRegisterForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$form->addText('name', 'Your nickname:')
			->addRule(Form::FILLED, 'Please choose a nickname.')
			->getLabelPrototype()->addClass('required');

		$form->addText('email', 'E-mail (optional):')
			->setType('email')
			->addCondition(Form::FILLED)
				->addRule(Form::EMAIL, 'Please provide valid e-mail address.');

		$form->addSubmit('save', 'Start session')
			->getControlPrototype()->addClass('btn btn-link cta large');

		$form->onSuccess[] = $this->onRegisterFormSuccess;
		$form->onValidate[] = $this->onRegisterFormValidate;

		return $form;
	}

	/**
	 * Validate registration form (unique e-mail required)
	 * @param Form $form
	 */
	public function onRegisterFormValidate(Form $form) {
		$values = $form->getValues();

		if (!$this->userService->isEmailUnique($values['email'], -1)) {
			$form->addError($this->translator->translate('User with selected e-mail address already exists.'));
			if ($this->isAjax())
				$this->invalidateControl('registerForm');
		}
	}

	/**
	 * Save registration form
	 * @param Form $form
	 */
	public function onRegisterFormSuccess(Form $form) {
		$values = $form->getValues();

		if (!$values['email'])
			// anonymous user - generate random e-mail
			$values['email'] = sprintf('system_%s@example.com', Strings::random(10));

		$user = new User();
		$user->setName($values['name']);
		$user->setDateRegistration(new \DateTime());
		$user->setEmail($values['email']);
		$user->setIsAnonymous(true);

		$user->addRole($this->userService->getRole(RoleEnum::STUDENT));

		if ($this->itilConfigurator->getAssignAnonymousUsersCreatorRole())
			$user->addRole($this->userService->getRole(RoleEnum::CREATOR));

		try {
			$this->userService->updateUser($user);
			$this->userService->commitChanges();

			$this->user->login($this->userService->getUserIdentity($user));

			$this->flashInfoMessage('Your session has been created.');

		} catch(\Exception $e) {
			$form->addError('Registration failed, try again.');
			$this->invalidateControl('registerForm');
		}

		if ($user->getId())
			$this->redirect('default:default');
	}

	#endregion

	#region "Logout"

	/**
	 * Logout user
	 */
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashInfoMessage('You have been signed out.');

		$this->redirect('default:default');
	}

	#endregion

	#region "My profile"

	/**
	 * User`s profile
	 */
	public function actionMyProfile() {
		/** @var User $user */
		$user = $this->user->getId();
		$this['profileForm']->setDefaults(array('name' => $user->getName(), 'email' => $user->getEmail()));
	}

	/**
	 * Profile form
	 * @return Form
	 */
	protected function createComponentProfileForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$form->addText('name', 'Name:');
		$form->addText('email', 'E-mail:')
			->setType('email')
			->addCondition(Form::FILLED)
				->addRule(Form::EMAIL, 'Please use valid e-mail address.');

		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->onProfileFormSuccess;

		return $form;
	}

	/**
	 * Save profile form
	 * @param Form $form
	 */
	public function onProfileFormSuccess(Form $form) {
		$values = $form->getValues();

		/** @var User $user */
		$user = $this->getDBUser();
		$user->setName($values['name']);
		$user->setEmail($values['email']);
		$this->userService->updateUser($user);

		$this->flashInfoMessage('Your profile has been saved.');

		$this->redirect('this');
	}

	#endregion


}
