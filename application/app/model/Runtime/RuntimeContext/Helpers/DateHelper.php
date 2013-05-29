<?php
/**
 * DateHelper.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 0:38
 */

namespace ITILSimulator\Runtime\RuntimeContext\Helpers;


use ITILSimulator\Runtime\ITILEnvironment;
use Nette\Object;

/**
 * Date helper for runtime context
 * @package ITILSimulator\Runtime\RuntimeContext\Helpers
 */
class DateHelper extends Object
{
	public function getFullYear() {
		return date('Y');
	}

	public function getTime() {
		return mktime();
	}

	public function getDate() {
		return date('j');
	}

	public function getDay() {
		return date('w');
	}

	public function getHours() {
		return date('H');
	}

	public function getMilliseconds() {
		$date = gettimeofday();
		return $date['usec'];
	}

	public function getMinutes() {
		return date('i');
	}

	public function getMonth() {
		return date('n');
	}

	public function getSeconds() {
		return date('s');
	}

	public function getToDateString() {
		return date('j.n.Y');
	}

	public function getToTimeString() {
		return date('H:i:s');
	}

	public function getInternalTime() {
		return ITILEnvironment::getInstance()->getInternalTime();
	}
}