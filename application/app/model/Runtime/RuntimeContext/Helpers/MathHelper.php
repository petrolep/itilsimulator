<?php
/**
 * MathHelper.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 3.5.13 17:15
 */

namespace ITILSimulator\Runtime\RuntimeContext\Helpers;


use Nette\Object;

/**
 * Math helper for runtime context
 * @package ITILSimulator\Runtime\RuntimeContext\Helpers
 */
class MathHelper extends Object
{
	public function abs($value) {
		return abs($value);
	}

	public function ceil($value) {
		return ceil($value);
	}

	public function floor($value) {
		return floor($value);
	}

	public function max() {
		return call_user_func_array('max', func_get_args());
	}

	public function min() {
		return call_user_func_array('min', func_get_args());
	}

	public function pow($x, $y) {
		return pow($x, $y);
	}

	public function random() {
		return rand();
	}

	public function round($value) {
		return round($value);
	}
}