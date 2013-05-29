<?php
/**
 * EvaluationEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 19.4.13 23:12
 */

namespace ITILSimulator\Runtime\Events;

/**
 * EventManager event about evaluation change
 * @package ITILSimulator\Runtime\Events
 */
class EvaluationEvent extends Event
{
	/** @var int */
	protected $points = 0;

	/** @var float */
	protected $money = 0.0;

	/**
	 * @param int $points
	 */
	public function setPoints($points)
	{
		$this->points = $points;
	}

	/**
	 * @return int
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @param float $money
	 */
	public function setMoney($money)
	{
		$this->money = $money;
	}

	/**
	 * @return float
	 */
	public function getMoney()
	{
		return $this->money;
	}


}