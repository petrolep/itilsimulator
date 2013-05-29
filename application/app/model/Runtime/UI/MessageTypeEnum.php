<?php
/**
 * MessageTypeEnum.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 23.4.13 19:31
 */

namespace ITILSimulator\Runtime\UI;


/**
 * UI messages type enumeration
 * @package ITILSimulator\Runtime\UI
 */
class MessageTypeEnum
{
	const INFO = 'info';
	const ERROR = 'error';
	const WARNING = 'warning';
	const SUCCESS = 'success';
	const EVALUATION = 'evaluation';

	private static $options = array(self::INFO => 'info', self::SUCCESS => 'success', self::WARNING => 'warning', self::ERROR => 'error');

	/**
	 * Get available types as array
	 * @return array
	 */
	public static function getOptions() {
		return self::$options;
	}

	/**
	 * Get type name from its value
	 * @param int $value
	 * @return string
	 */
	public static function getNameFromValue($value) {
		if (isset(self::$options[$value]))
			return self::$options[$value];

		return '';
	}
}