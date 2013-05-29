<?php
/**
 * WorkflowActivityFormControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 14:14
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\BaseControl;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Workflow\Activities\CustomActivity;
use ITILSimulator\Entities\Workflow\Activities\EvaluationActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\Activities\IncidentActivity;
use ITILSimulator\Entities\Workflow\Activities\MessageActivity;
use ITILSimulator\Entities\Workflow\Activities\ProblemActivity;
use ITILSimulator\Entities\Workflow\Workflow;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\InvalidArgumentException;

/**
 * Control for workflow activity form
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class WorkflowActivityFormControl extends BaseControl
{
	const MESSAGE = 'message';
	const FLOW = 'flow';

	/** @var array Event onSuccess */
	public $onSuccess = array();

	/** @var Workflow */
	protected $workflow;

	/** @var WorkflowActivity */
	protected $workflowActivity;

	/** @var OperationCategory[] */
	protected $availableCategories = array();

	/**
	 * Creates workflow activity form.
	 * @return EvaluationActivityForm|FlowActivityForm|IncidentActivityForm|MessageActivityForm|ProblemActivityForm
	 * @throws \Nette\InvalidArgumentException
	 */
	public function createComponentActivityForm() {
		$form = $this->getForm($this->workflowActivity);
		$form->setTranslator($this->translator);

		if (!$form)
			throw new InvalidArgumentException('Invalid activity type ' . $this->type);

		foreach ($this->onSuccess as $onSuccess) {
			$form->onSuccess[] = $onSuccess;
		}

		$form->setWorkflow($this->workflow);
		$form->setWorkflowActivity($this->workflowActivity);
		$form->setDefaultValues($this->workflowActivity);
		$form->setAvailableCategories($this->availableCategories);

		return $form;
	}

	/**
	 * Based on activity type return corresponding form.
	 * @param $workflowActivity
	 * @return EvaluationActivityForm|FlowActivityForm|IncidentActivityForm|MessageActivityForm|ProblemActivityForm
	 */
	protected function getForm($workflowActivity) {
		switch ($workflowActivity) {
			case ($workflowActivity instanceof MessageActivity):
				return new MessageActivityForm();

			case ($workflowActivity instanceof FlowActivity):
				return new FlowActivityForm();

			case ($workflowActivity instanceof EvaluationActivity):
				return new EvaluationActivityForm();

			case ($workflowActivity instanceof IncidentActivity):
				return new IncidentActivityForm();

			case ($workflowActivity instanceof ProblemActivity):
				return new ProblemActivityForm();

			case ($workflowActivity instanceof CustomActivity):
				return new CustomActivityForm();
		}
	}

	protected function getTemplateName() {
		$fullName = get_class($this['activityForm']);
		$name = substr($fullName, strrpos($fullName, '\\') + 1);

		return sprintf('%s/%s.latte', __DIR__, ucfirst($name));
	}

	public function render()
	{
		$template = $this->getCustomTemplate($this->getTemplateName());
		$template->activityForm = $this->getComponent('activityForm');
		$template->render();
	}

	#region "Get & set"

	/**
	 * Set workflow
	 * @param $workflow
	 */
	public function setWorkflow($workflow)
	{
		$this->workflow = $workflow;
	}

	/**
	 * Return workflow
	 * @return Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	/**
	 * Set workflow activity to be edited
	 * @param $workflowActivity
	 */
	public function setWorkflowActivity($workflowActivity)
	{
		$this->workflowActivity = $workflowActivity;
	}

	/**
	 * Return edited workflow activity
	 * @return WorkflowActivity
	 */
	public function getWorkflowActivity()
	{
		return $this->workflowActivity;
	}

	/**
	 * Set available operation categories
	 * @param $availableCategories
	 */
	public function setAvailableCategories($availableCategories)
	{
		$this->availableCategories = $availableCategories;
	}

	/**
	 * Return available operation categories
	 * @return array|OperationCategory[]
	 */
	public function getAvailableCategories()
	{
		return $this->availableCategories;
	}

	#endregion
}