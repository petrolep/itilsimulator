<?php
/**
 * ICustomControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 13:58
 */

namespace ITILSimulator\Base;

/**
 * Custom control interface for controls using custom template.
 * @package ITILSimulator\Base
 */
interface ICustomControl
{
	/**
	 * @param $file
	 * @return \Nette\Templating\Template
	 */
	function getCustomTemplate($file);
}