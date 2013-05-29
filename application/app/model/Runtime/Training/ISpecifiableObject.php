<?php
/**
 * ISpecificableObject.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 14:03
 */

namespace ITILSimulator\Runtime\Training;

/**
 * Interface for objects which have custom specification
 * @package ITILSimulator\Runtime\Training
 */
interface ISpecifiableObject {
	/**
	 * Whether the specification was modified
	 * @return mixed
	 */
	public function isSpecificationChanged();
}