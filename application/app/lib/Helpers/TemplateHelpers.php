<?php
/**
 * TemplateHelpers.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.4.13 11:18
 */

namespace ITILSimulator\Base;


use ITILSimulator\Runtime\Training\PriorityEnum;

/**
 * Template helpers to be used in Latte templates.
 * @package ITILSimulator\Base
 */
class TemplateHelpers
{
	public static $currency = 'Kč';

	/**
	 * Currency helper
	 * @param $value
	 * @return string
	 */
	public static function currency($value)
	{
		return str_replace(" ", "\xc2\xa0", number_format($value, 0, "", " ")) . "\xc2\xa0" . self::$currency;
	}

	public static function emptyCurrency()
	{
		return self::$currency;
	}

	public static function priority($value)
	{
		return PriorityEnum::getNameFromValue($value);
	}
}