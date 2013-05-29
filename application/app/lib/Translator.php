<?php
/**
 * Translator.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:59
 */

namespace ITILSimulator\Base;

use Nette\Localization\ITranslator;

/**
 * Simple translator.
 * @package ITILSimulator\Base
 */
class Translator implements ITranslator
{
	public function translate($message, $count = NULL)
	{
		return $message;
	}
}