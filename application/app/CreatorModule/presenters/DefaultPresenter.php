<?php
/**
 * DefaultPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 14:24
 */

namespace CreatorModule;


use ITILSimulator\Creator\Presenters\CreatorPresenter;

/**
 * Homepage creator presenter.
 * @package CreatorModule
 */
class DefaultPresenter extends CreatorPresenter
{
	public function actionDefault()
	{
		$this->redirect('training:list');
	}
}