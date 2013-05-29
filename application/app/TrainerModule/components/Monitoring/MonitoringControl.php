<?php
/**
 * MonitoringControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 12:37
 */

namespace ITILSimulator\Trainer\Components\Monitoring;

use ITILSimulator\Runtime\Simulator\Monitoring\ConfigurationItemAttributeStatistics;
use ITILSimulator\Runtime\Simulator\Monitoring\ConfigurationItemStatistics;
use ITILSimulator\Runtime\Training\ActiveService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Components\TrainerControl;

/**
 * History of configuration items health
 * @package ITILSimulator\Trainer\Components\Monitoring
 */
class MonitoringControl extends TrainerControl
{
	#region "Properties"

	/** @persistent */
	public $serviceId;

	/** @var TrainingService */
	protected $trainingService;

	/** @var ActiveService[] */
	protected $activeServices;

	/** @var int */
	protected $trainingStepId;

	/** @var int */
	protected $historyTimeLimit = 1000;

	#endregion

	/**
	 * Refresh graph
	 */
	public function handleReload() {
		$this->invalidateControl('monitoring');
	}

	public function render()
	{
		$template = $this->getCustomTemplate(__FILE__);

		if ($selectedService = $this->getSelectedService()) {
			$data = $this->getStatistics($selectedService);
			$template->data = $data;

		} else {
			$template->data = FALSE;
		}

		$template->services = $this->getServices();
		$template->activeService = $this->serviceId;
		$template->render();
	}

	#region "Helper methods"

	protected function getServices() {
		$result = array();
		foreach ($this->activeServices as $activeService)
			$result[] = $activeService->getService();

		return $result;
	}

	protected function getStatistics(ActiveService $service) {
		$maxTime = 0;
		$data = array();
		foreach ($service->getConfigurationItems() as $ci) {
			$stats = new ConfigurationItemStatistics();
			$stats->setId($ci->getConfigurationItemId());
			$data[] = $stats;

			/** @var $attrStats ConfigurationItemAttributeStatistics[] */
			$attrStats = array();

			$history = $this->trainingService->getConfigurationItemAttributesHistory($ci->getConfigurationItemId(), $this->trainingStepId);
			foreach ($history as $record) {
				$stats->setName($record->getConfigurationItemName());

				foreach ($record->getAttributes() as $key => $attribute) {
					if (!isset($attrStats[$key])) {
						$attrStats[$key] = new ConfigurationItemAttributeStatistics($attribute->getName());
						$stats->addAttributeHistory($attrStats[$key]);
					}

					$attrStats[$key]->addHistory($record->getInternalTime(), $attribute->getCurrentValue());
					if ($record->getInternalTime() > $maxTime) {
						$maxTime = $record->getInternalTime();
					}
				}
			}
		}

		if ($this->historyTimeLimit)
			$data = $this->limitByTime($maxTime - $this->historyTimeLimit, $data);

		return $data;
	}

	protected function limitByTime($time, $data) {
		foreach ($data as $stats) {
			/** @var $stats ConfigurationItemStatistics */
			foreach ($stats->getAttributesHistory() as $attr) {
				$attr->filterHistory($time);
			}
		}

		return $data;
	}

	/**
	 * @return ActiveService|null
	 */
	protected function getSelectedService()
	{
		/** @var ActiveService $selectedService */
		$selectedService = NULL;
		foreach ($this->activeServices as $service) {
			if ($service->getServiceId() == $this->serviceId) {
				$selectedService = $service;
				break;
			}
		}

		if (!$selectedService) {
			$selectedService = reset($this->activeServices);
			if ($selectedService)
				$this->serviceId = $selectedService->getServiceId();
		}

		return $selectedService;
	}

	#endregion

	#region "Get & set"

	/**
	 * @param TrainingService $trainingService
	 */
	public function setTrainingService(TrainingService $trainingService)
	{
		$this->trainingService = $trainingService;
	}

	/**
	 * @return TrainingService
	 */
	public function getTrainingService()
	{
		return $this->trainingService;
	}

	/**
	 * @param \ITILSimulator\Runtime\Training\ActiveService[] $activeServices
	 */
	public function setActiveServices($activeServices)
	{
		$this->activeServices = $activeServices;
	}

	/**
	 * @return \ITILSimulator\Runtime\Training\ActiveService[]
	 */
	public function getActiveServices()
	{
		return $this->activeServices;
	}

	/**
	 * @param int $trainingStepId
	 */
	public function setTrainingStepId($trainingStepId)
	{
		$this->trainingStepId = $trainingStepId;
	}

	/**
	 * @return int
	 */
	public function getTrainingStepId()
	{
		return $this->trainingStepId;
	}

	/**
	 * @param int $historyTimeLimit
	 */
	public function setHistoryTimeLimit($historyTimeLimit)
	{
		$this->historyTimeLimit = $historyTimeLimit;
	}

	/**
	 * @return int
	 */
	public function getHistoryTimeLimit()
	{
		return $this->historyTimeLimit;
	}
	#endregion
}