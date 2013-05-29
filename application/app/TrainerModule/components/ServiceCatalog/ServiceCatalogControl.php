<?php
/**
 * ServiceCatalogControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.4.13 10:59
 */

namespace ITILSimulator\Trainer\Components\ServiceCatalog;


use ITILSimulator\Runtime\Session\ActiveSession;
use ITILSimulator\Trainer\Components\TrainerControl;

/**
 * Service catalog control
 * @package ITILSimulator\Trainer\Components\ServiceCatalog
 */
class ServiceCatalogControl extends TrainerControl
{
	#region "Properties"

	/** @var ActiveSession */
	protected $activeSession;

	#endregion

	#region "Service catalog actions"

	/**
	 * Restart configuration item
	 * @param int $id Configuration item ID
	 */
	public function handleRestartCI($id) {
		foreach ($this->activeSession->getActiveServices() as $activeService) {
			foreach ($activeService->getConfigurationItems() as $activeCI) {
				if ($activeCI->getConfigurationItemId() == $id) {
					$activeCI->restart();

					break;
				}
			}
		}

		$this->invalidateServicesPanel();
	}

	/**
	 * Replace configuration item
	 * @param int $id Configuration item ID
	 */
	public function handleReplaceCI($id) {
		foreach ($this->activeSession->getActiveServices() as $activeService) {
			foreach ($activeService->getConfigurationItems() as $activeCI) {
				if ($activeCI->getConfigurationItemId() == $id) {
					$activeCI->replace();

					break;
				}
			}
		}

		$this->invalidateServicesPanel();
	}

	#endregion

	public function invalidateServicesPanel() {
		$this->invalidateControl('serviceCatalogInner');
	}

	/**
	 * Render control
	 */
	public function render()
	{
		$template = $this->getCustomTemplate(__FILE__);
		$services = $this->activeSession->getActiveServices();
		$template->services = $services;
		$hasDesignData = false;
		foreach ($services as $activeService) {
			if ($activeService->getService()->getGraphicDesignData()) {
				$hasDesignData = true;
			}
		}
		$template->hasDesignData = $hasDesignData;
		$template->render();
	}

	#region "Get & set"

	/**
	 * @param \ITILSimulator\Runtime\Session\ActiveSession $activeSession
	 */
	public function setActiveSession($activeSession)
	{
		$this->activeSession = $activeSession;
	}

	/**
	 * @return \ITILSimulator\Runtime\Session\ActiveSession
	 */
	public function getActiveSession()
	{
		return $this->activeSession;
	}

	#endregion
}