<?php
/**
 * EvaluationActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 19.4.13 23:07
 */

namespace ITILSimulator\Entities\Workflow\Activities;


use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\EvaluationEvent;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Evaluation activity (Doctrine entity). Changes user evaluation (points + budget).
 * Inherits WorkflowActivity.
 * @Entity
 */
class EvaluationActivity extends WorkflowActivity
{
	#region "Properties"

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $points = 0;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $money = 0.0;

	#endregion

	#region "Get & set"

	/**
	 * @param float $money
	 */
	public function setMoney($money)
	{
		$this->money = $money;
	}

	/**
	 * @return float
	 */
	public function getMoney()
	{
		return $this->money;
	}

	/**
	 * @param int $points
	 */
	public function setPoints($points)
	{
		$this->points = $points;
	}

	/**
	 * @return int
	 */
	public function getPoints()
	{
		return $this->points;
	}

	#endregion

	#region "Events"

	/**
	 * Executed when the workflow activity starts. Fires ACTIVITY_EVALUATION_CREATED event.
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null|void
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		$event = new EvaluationEvent();
		$event->setPoints($this->getPoints());
		$event->setMoney($this->getMoney());

		$context->getEventManager()->dispatch(EventTypeEnum::ACTIVITY_EVALUATION_CREATED, $event);
		$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);

		parent::onStart($context);
	}

	#endregion
  
	public function __toString() {
		return sprintf('[EvaluationActivity] #%d', $this->id);
	}
}