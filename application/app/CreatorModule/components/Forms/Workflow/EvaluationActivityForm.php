<?php
/**
 * FlowActivityForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 11:27
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Entities\Workflow\Activities\EvaluationActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\Forms\Form;

/**
 * Evaluation activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class EvaluationActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return WorkflowActivity
	 */
	public function getWorkflowActivity()
	{
		if (!$this->workflowActivity)
			$this->workflowActivity = new EvaluationActivity();

		$this->workflowActivity->setPoints($this['points']->getValue());
		$this->workflowActivity->setMoney($this['money']->getValue());

		return $this->workflowActivity;
	}

	/**
	 * Set default form values
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity)
	{
		if ($workflowActivity instanceof EvaluationActivity) {
			/** @var $entity EvaluationActivity */
			$entity = $workflowActivity;
			$this->setDefaults(array(
				'points' => $entity->getPoints(),
				'money' => $entity->getMoney()
			));
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('points', 'Number of points:')
			->setType('number')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Value must be a valid integer number.');

		$this->addText('money', 'Money change:')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Value must be a valid float number.');

		$this->addSubmit('save', 'Save evaluation');
	}

	#endregion
}