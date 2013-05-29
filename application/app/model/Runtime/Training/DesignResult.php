<?php
/**
 * DesignResult.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 22:02
 */

namespace ITILSimulator\Runtime\Training;


use Nette\Object;

/**
 * Result of design scenario
 * @package ITILSimulator\Runtime\Training
 */
class DesignResult extends Object
{
	/** @var array created connections between configuration items */
	protected $connections = array();

	/** @var array used positions of configuration items */
	protected $positions = array();

	/** @var int total purchase costs */
	protected $purchaseCosts = 0;

	/** @var int total operation costs */
	protected $operationCosts = 0;

	public function setConnections($connections)
	{
		$this->connections = $connections;
	}

	public function getConnections()
	{
		return $this->connections;
	}

	public function setOperationCosts($operationCosts)
	{
		$this->operationCosts = $operationCosts;
	}

	public function getOperationCosts()
	{
		return $this->operationCosts;
	}

	public function setPositions($positions)
	{
		$this->positions = $positions;
	}

	public function getPositions()
	{
		return $this->positions;
	}

	public function setPurchaseCosts($purchaseCosts)
	{
		$this->purchaseCosts = $purchaseCosts;
	}

	public function getPurchaseCosts()
	{
		return $this->purchaseCosts;
	}

}