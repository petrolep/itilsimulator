<?php
/**
 * ConfigurationItemAttributeStatistics.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 13:55
 */

namespace ITILSimulator\Runtime\Simulator\Monitoring;


use Nette\Object;

/**
 * History of values of a custom attribute of a configuration item
 * @package ITILSimulator\Runtime\Simulator\Monitoring
 */
class ConfigurationItemAttributeStatistics extends Object
{
	protected $history = array();

	protected $name;

	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Add new history value
	 * @param int $time Internal time
	 * @param int $value Value
	 */
	public function addHistory($time, $value) {
		$this->history[] = array($time, (int)$value);
	}

	/**
	 * Filter out records older than given time
	 * @param int $maxTime Maximum history age
	 */
	public function filterHistory($maxTime) {
		$this->history = array_filter($this->history, function($el) use ($maxTime) { return $el[0] >= $maxTime;});
	}

	/**
	 * @return ConfigurationItemAttributeStatistics
	 */
	public function getHistory()
	{
		return $this->history;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
}