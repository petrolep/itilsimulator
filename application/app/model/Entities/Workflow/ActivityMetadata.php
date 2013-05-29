<?php
/**
 * ActivityMetadata.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 18:11
 */

namespace ITILSimulator\Entities\Workflow;


use ITILSimulator\Base\Position;
use Nette\Object;

/**
 * Helper class to store workflow activities metadata, such as their position in workflow designer.
 * @package ITILSimulator\Entities\Workflow
 */
class ActivityMetadata extends Object
{
	/** @var Position */
	protected $position;

	public function __construct() {
		$this->position = new Position(0, 0);
	}

	/**
	 * @param \ITILSimulator\Base\Position $position
	 */
	public function setPosition($position)
	{
		$this->position = $position;
	}

	/**
	 * @return \ITILSimulator\Base\Position
	 */
	public function getPosition()
	{
		return $this->position;
	}
}