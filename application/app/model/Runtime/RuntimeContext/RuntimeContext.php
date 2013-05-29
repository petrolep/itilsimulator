<?php
/**
 * RuntimeContext.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.4.13 12:58
 */

namespace ITILSimulator\Runtime\RuntimeContext;


use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventManager;
use Nette\Object;

/**
 * Abstract Runtime class for custom behavior code.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
abstract class RuntimeContext extends Object
{
	/** @var EventManager */
	protected $eventManager;

	/**
	 * @param string $function
	 * @param Event $event
	 * @return mixed
	 */
	public abstract function execute($function, $event);

	#region "Get & set"

	/**
	 * @param \ITILSimulator\Runtime\Events\EventManager $eventManager
	 */
	public function setEventManager($eventManager)
	{
		$this->eventManager = $eventManager;
	}

	/**
	 * @return \ITILSimulator\Runtime\Events\EventManager
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}

	#endregion

	protected function registerErrorHandler() {
		set_error_handler(array($this, 'exceptions_error_handler'));
	}

	protected function restoreErrorHandler() {
		restore_error_handler();
	}

	// http://stackoverflow.com/questions/5373780/how-to-catch-this-error-notice-undefined-offset-0
	public function exceptions_error_handler($severity, $message, $filename, $lineno)
	{
		if (error_reporting() == 0) {
			return;
		}
		if (error_reporting() & $severity) {
			throw new \Exception($message);
		}
	}
}