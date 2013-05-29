<?php
/**
 * ServiceAccountant.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 1.5.13 22:17
 */

namespace ITILSimulator\Runtime\Simulator;


use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Runtime\Session\ActiveSession;
use Nette\Object;

/**
 * Accountant to create bank statements and generate incomes and expenses for service operation
 * @package ITILSimulator\Runtime\Simulator
 */
class ServiceAccountant extends Object
{
	/** @var \ITILSimulator\Runtime\Session\ActiveSession */
	protected $activeSession;

	/** @var int */
	protected $currentTime;

	/** @var int new statement generated every X seconds */
	protected $accountingInterval = 60;

	public function __construct(ActiveSession $activeSession, $currentTime, $accountingInterval)
	{
		$this->activeSession = $activeSession;
		$this->currentTime = $currentTime;
		$this->accountingInterval = $accountingInterval;
	}

	/**
	 * Run service accountant
	 * @return ServiceAccountantResult|null
	 */
	public function run()
	{
		$scenarioStep = $this->activeSession->getTrainingStep()->getLastValidScenarioStep();
		if ($scenarioStep->getServicesSettlementTime() + $this->accountingInterval < $this->currentTime) {
			$scenarioStep->setServicesSettlementTime($this->currentTime);

			$income = 0;
			$expenses = 0;
			foreach ($this->activeSession->getActiveServices() as $service) {
				$income += $service->getHealthLevel() / 100 * $service->getSpecification()->getEarnings();
				$expenses += $service->getOperationalCosts();
			}

			$scenarioStep->setBudget($scenarioStep->getBudget() + $income - $expenses);
			$scenarioStep->setInternalTime($this->currentTime);

			return new ServiceAccountantResult($income, $expenses);
		}

		return null;
	}
}