<?php
/**
 * ActiveWorkflowActivityFacade.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 3.5.13 20:22
 */

namespace ITILSimulator\Runtime\RuntimeContext;


use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Runtime\Events\ConfigurationItemStateChangeEvent;
use ITILSimulator\Runtime\Events\ConfigurationItemStateRequestEvent;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationIncidentStateChangeEvent;
use ITILSimulator\Runtime\Events\OperationProblemEvent;
use ITILSimulator\Runtime\Events\OperationProblemStateChangeEvent;
use ITILSimulator\Runtime\Workflow\ActiveWorkflowActivity;
use Nette\MemberAccessException;
use Nette\Object;

/**
 * Facade for public access from custom behavior code in workflow activities.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
class ActiveWorkflowActivityFacade extends Object
{
	#region "Properties"

	/** @var ActiveWorkflowActivity  */
	protected $activeWorkflowActivity;

	/** @var WorkflowActivityRuntimeContext  */
	protected $runtimeContext;

	/** @var WorkflowActivitySpecification  */
	protected $specification;

	#endregion

	public function __construct(ActiveWorkflowActivity $activeWorkflowActivity, WorkflowActivityRuntimeContext $runtimeContext) {
		$this->activeWorkflowActivity = $activeWorkflowActivity;
		$this->runtimeContext = $runtimeContext;
		$this->specification = $activeWorkflowActivity->getSpecification();
	}

	#region "Custom data"

	/**
	 * Return custom data
	 * @param string|null $key If key is provided, data with selected key is returned. Otherwise array of all keys are returned.
	 * @return array|null
	 */
	public function getData($key = NULL) {
		if ($key)
			return $this->specification->getData($key);

		return NULL;
	}

	/**
	 * Set custom data
	 * @param string $key Name of value
	 * @param string $value Value
	 */
	public function setData($key = NULL, $value = NULL) {
		if ($key)
			$this->specification->setDataValue($key, $value);
	}

	/**
	 * Return property value or value from data collection (can be used as a shortcut for "getData")
	 * @param $name
	 * @return array|mixed|null
	 */
	public function &__get($name)
	{
		try {
			return parent::__get($name);

		} catch(MemberAccessException $e) {
			$val = $this->specification->getData($name);
			return $val;
		}
	}

	/**
	 * Set property value or value from data collection (can be used as a shortcut for "setData")
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		try {
			parent::__set($name, $value);

		} catch(MemberAccessException $e) {
			$this->specification->setDataValue($name, $value);
		}
	}

	#endregion

	#region "Get & set"

	public function setDescription($value) {
		$this->specification->setDescription($value);
	}

	public function getDescription() {
		return $this->specification->getDescription();
	}

	#endregion

	#region "Operations"

	/**
	 * Finish workflow activity
	 */
	public function finish() {
		$this->activeWorkflowActivity->finish();
	}

	/**
	 * Cancel workflow activity
	 */
	public function cancel() {
		$this->activeWorkflowActivity->cancel();
	}

	/**
	 * Set value of configuration item
	 * @param string $serviceCode Service code
	 * @param string $configurationItemCode Configuration item Code
	 * @param string $name Property name
	 * @param string $value Property value
	 */
	public function setCIValue($serviceCode, $configurationItemCode, $name, $value) {
		$event = new ConfigurationItemStateChangeEvent($serviceCode, $configurationItemCode, $name, $value);

		$this->runtimeContext->dispatchEvent(EventTypeEnum::CONFIGURATION_ITEM_CHANGE, $event);
	}

	public function getCIValue($serviceCode, $configurationItemCode, $name) {
		$event = new ConfigurationItemStateRequestEvent($serviceCode, $configurationItemCode, $name);

		$this->runtimeContext->dispatchEvent(EventTypeEnum::CONFIGURATION_ITEM_REQUEST, $event);

		return $event->getValue();
	}

	/**
	 * Mark incident as solved
	 * @param $incidentReferenceNumber
	 */
	public function solveIncident($incidentReferenceNumber) {
		$this->setIncidentStatus($incidentReferenceNumber, OperationIncident::STATUS_SOLVED);
	}

	/**
	 * Mark incident as closed
	 * @param $incidentReferenceNumber
	 */
	public function closeIncident($incidentReferenceNumber) {
		$this->setIncidentStatus($incidentReferenceNumber, OperationIncident::STATUS_CLOSED);
	}

	private function setIncidentStatus($incidentReferenceNumber, $status) {
		$event = new OperationIncidentStateChangeEvent($incidentReferenceNumber, $status);

		$this->runtimeContext->dispatchEvent(EventTypeEnum::ACTIVITY_INCIDENT_CHANGE, $event);
	}

	/**
	 * Mark problem as closed
	 * @param $problemReferenceNumber
	 */
	public function closeProblem($problemReferenceNumber) {
		$event = new OperationProblemStateChangeEvent($problemReferenceNumber, OperationProblem::STATUS_CLOSED);

		$this->runtimeContext->dispatchEvent(EventTypeEnum::ACTIVITY_PROBLEM_CHANGE, $event);
	}

	#endregion
}