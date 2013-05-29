<?php
/**
 * Position.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 18:12
 */

namespace ITILSimulator\Base;


use Nette\Object;

/**
 * Position container holding "x" and "y" coordinates.
 * @package ITILSimulator\Base
 */
class Position extends Object
{
	/** @var int */
	protected $x;

	/** @var int */
	protected $y;

	public function __construct($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	public function setX($x)
	{
		$this->x = $x;
	}

	public function getX()
	{
		return $this->x;
	}

	public function setY($y)
	{
		$this->y = $y;
	}

	public function getY()
	{
		return $this->y;
	}


}