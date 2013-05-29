<?php
/**
 * RuntimeContextHelper.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 3.5.13 17:15
 */

namespace ITILSimulator\Runtime\RuntimeContext;


use ITILSimulator\Runtime\RuntimeContext\Helpers\DateHelper;
use ITILSimulator\Runtime\RuntimeContext\Helpers\MathHelper;
use Nette\Object;

/**
 * Helper class available in custom behavior code.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
class RuntimeContextHelper extends Object
{
	/** @var \ITILSimulator\Runtime\RuntimeContext\Helpers\MathHelper Math helper */
	public $math;

	/** @var \ITILSimulator\Runtime\RuntimeContext\Helpers\DateHelper Date helper */
	public $date;

	public function __construct() {
		$this->math = new MathHelper();
		$this->date = new DateHelper();
	}
}