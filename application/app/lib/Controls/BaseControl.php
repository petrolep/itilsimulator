<?php
/**
 * BaseControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 13:21
 */

namespace ITILSimulator\Base;


use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Base control.
 * Set default template properties and helpers.
 * @package ITILSimulator\Base
 */
abstract class BaseControl extends Control implements ICustomControl
{
	/** @var ITranslator */
	protected $translator;

	abstract public function render();

	function getCustomTemplate($file) {
		$template = $this->template;
		$template->registerHelper('currency', 'ITILSimulator\Base\TemplateHelpers::currency');
		$template->registerHelper('priority', 'ITILSimulator\Base\TemplateHelpers::priority');

		$template->setFile(str_replace('.php', '.latte', $file));

		if ($this->translator)
			$template->setTranslator($this->translator);

		return $template;
	}

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 */
	public function setTranslator($translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}
}