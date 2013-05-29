<?php
/**
 * FormHelper.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 28.4.13 23:04
 */

namespace ITILSimulator\Base;


use Nette\Application\UI\Form;
use Nette\Object;

/**
 * Form helper to populate and save values in form from objects.
 * @package ITILSimulator\Base
 */
class FormHelper
{
	/**
	 * Set default form values from given source object.
	 * @param Form $form
	 * @param Object $source
	 */
	public static function setDefaultValues(Form $form, Object $source)
	{
		$defaultValues = array();
		foreach ($form->getValues() as $key => $value) {
			$defaultValues[$key] = $source->$key;
		}
		$form->setDefaults($defaultValues);
	}

	/**
	 * Save values from form to target object.
	 * @param Object $target
	 * @param $values
	 */
	public static function updateValues(Object $target, $values)
	{
		foreach ($values as $key => $value) {
			$target->$key = $value;
		}
	}
}