<?php
/**
 * ServiceAccountantResult.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 1.5.13 22:24
 */

namespace ITILSimulator\Runtime\Simulator;


use Nette\Object;

/**
 * Result of service accountant
 * @package ITILSimulator\Runtime\Simulator
 */
class ServiceAccountantResult extends Object
{
	protected $income;
	protected $expenses;

	public function __construct($income, $expenses)
	{
		$this->income = $income;
		$this->expenses = $expenses;
	}

	public function setExpenses($expenses)
	{
		$this->expenses = $expenses;
	}

	public function getExpenses()
	{
		return $this->expenses;
	}

	public function setIncome($income)
	{
		$this->income = $income;
	}

	public function getIncome()
	{
		return $this->income;
	}


}