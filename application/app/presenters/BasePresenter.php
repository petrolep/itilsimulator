<?php
/**
 * BasePresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 1.5.13 21:57
 */

namespace ITILSimulator\Base;

use GettextTranslator\Gettext;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use ITILSimulator\Services\UserService;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
	#region "Properties"

	/** @var UserService */
	protected $userService;

	/** @persistent */
	public $lang;

	/** @var Gettext */
	protected $translator;

	/** @var ITILConfigurator */
	protected $itilConfigurator;

	#endregion

	#region "Lifecycle methods

	public function injectBase(UserService $userService, Gettext $translator, ITILConfigurator $configurator)
	{
		$this->userService = $userService;
		$this->translator = $translator;
		$this->itilConfigurator = $configurator;
	}

	public function startup()
	{
		parent::startup();

		// if not set, the default language will be used
		if (!isset($this->lang)) {
			$this->lang = $this->translator->getLang();
		} else {
			$this->translator->setLang($this->lang);
		}

		$this->template->setTranslator($this->translator);

		$this->template->user = $this->user->isLoggedIn() ? $this->user->getId() : NULL;
		$this->template->userIsStudent = $this->user->isInRole(RoleEnum::STUDENT);
		$this->template->userIsAdmin = $this->user->isInRole(RoleEnum::ADMIN);
		$this->template->userIsCreator = $this->user->isInRole(RoleEnum::CREATOR);
		$this->template->registerHelper('currency', 'ITILSimulator\Base\TemplateHelpers::currency');
		$this->template->registerHelper('priority', 'ITILSimulator\Base\TemplateHelpers::priority');

		$this->template->isAjax = $this->isAjax();
	}

	#endregion

	/**
	 * Force user to log in
	 */
	protected function requireLogin() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect(':trainer:user:login');
		}
	}

	/**
	 * Display "Not allowed" page
	 */
	protected function forbidden() {
		$this->forward(':trainer:user:forbidden');
	}

	/**
	 * Return JSON response as boolean status "ok/error"
	 * @param $valid
	 */
	public function jsonResponseStatus($valid) {
		$this->jsonResponse(array('status' => $valid ? 'ok' : 'error', 'isValid' => $valid));
	}

	/**
	 * Return JSON response
	 * @param $data
	 */
	public function jsonResponse($data) {
		if ($data && !is_array($data)) {
			$data = array('status' => $data);
		}

		$this->sendResponse(new JsonResponse($data));
	}

	/**
	 * Create info flash message (UI message informing user)
	 * @param string $message Message text (will be localized)
	 * @param array $arguments Arguments to be inserted into the message
	 * @return object
	 */
	public function flashInfoMessage()
	{
		$message = call_user_func_array(array($this->translator, 'translate'), func_get_args());

		return $this->flashMessage($message, 'info');
	}

	/**
	 * Create flash message (UI message informing user)
	 * @param string $message
	 * @param string $type (info/error)
	 * @return object|\stdClass
	 */
	public function flashMessage($message, $type = 'info')
	{
		$id = $this->getParameterId('flash');
		$messages = $this->getPresenter()->getFlashSession()->$id;
		$messages[] = $flash = (object) array(
			'message' => $message,
			'type' => $type,
			'guid' => Strings::random(6), // unique message identifier
		);
		$this->getTemplate()->flashes = $messages;
		$this->getPresenter()->getFlashSession()->$id = $messages;

		return $flash;
	}

	/**
	 * Reload current user from database
	 * @return User
	 */
	protected function getDBUser() {
		return $this->userService->refreshUserIdentity($this->user->getId());
	}

	/**
	 * Return ID of current user
	 * @return int
	 */
	protected function getUserId()
	{
		return $this->getUserIdentity()->getId();
	}

	/**
	 * Return User object of current user
	 * @return User
	 */
	protected function getUserIdentity()
	{
		return $this->user->getId();
	}
}
