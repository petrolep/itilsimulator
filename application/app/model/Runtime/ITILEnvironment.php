<?php
/**
 * ITILEnvironment.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 0:50
 */

namespace ITILSimulator\Runtime;


/**
 * Helper class to have access to current internal time
 * @package ITILSimulator\Runtime
 */
class ITILEnvironment
{
	private static $instance;
	private $internalTime;

	private function __construct() { }

	public static function getInstance() {
		if (!self::$instance)
			self::$instance = new ITILEnvironment();

		return self::$instance;
	}

	public function setInternalTime($internalTime)
	{
		$this->internalTime = $internalTime;
	}

	public function getInternalTime()
	{
		return $this->internalTime;
	}
}