<?php
/**
 * PriorityEnum.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 15:21
 */

namespace ITILSimulator\Runtime\Training;

/**
 * Priority enumeration
 * @package ITILSimulator\Runtime\Training
 */
class PriorityEnum
{
	const CRITICAL = 1;
	const HIGH = 2;
	const MEDIUM = 3;
	const LOW = 4;

	private static $options = array(self::LOW => 'low', self::MEDIUM => 'medium', self::HIGH => 'high', self::CRITICAL => 'critical');

	/**
	 * Get available priorities as array
	 * @return array
	 */
	public static function getOptions() {
		return self::$options;
	}

	/**
	 * Get priority name from its value
	 * @param int $value
	 * @return string
	 */
	public static function getNameFromValue($value) {
		if (isset(self::$options[$value]))
			return self::$options[$value];

		return '';
	}
}