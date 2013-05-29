<?php
/**
 * FlowActivityForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 11:27
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Entities\Workflow\Activities\CustomActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\Application\UI\Form;

/**
 * Custom activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class CustomActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return CustomActivity
	 */
	public function getWorkflowActivity()
	{
		if (!$this->workflowActivity)
			$this->workflowActivity = new CustomActivity();

		foreach ($this->getValues() as $key => $value) {
			$this->workflowActivity->$key = $value;
		}

		$this->workflowActivity->setOnEvent($this->compiledEvents['onEvent']);
		$this->workflowActivity->setOnStart($this->compiledEvents['onStart']);
		$this->workflowActivity->setOnFinish($this->compiledEvents['onFinish']);
		$this->workflowActivity->setOnCancel($this->compiledEvents['onCancel']);
		$this->workflowActivity->setOnFlow($this->compiledEvents['onFlow']);

		return $this->workflowActivity;
	}

	/**
	 * Set default form values
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity)
	{
		if ($workflowActivity instanceof CustomActivity) {
			FormHelper::setDefaultValues($this, $workflowActivity);
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('description', 'Description:');
		$this->addTextArea('onEventRaw', 'onEvent:', 80, 25);
		$this->addTextArea('onStartRaw', 'onStart:', 80, 25);
		$this->addTextArea('onFlowRaw', 'onFlow:', 80, 25);
		$this->addTextArea('onFinishRaw', 'onFinish:', 80, 25);
		$this->addTextArea('onCancelRaw', 'onCancel:', 80, 25);

		$this->addSubmit('save', 'Save activity');

		$this->onValidate[] = $this->onValidateForm;
	}

	#endregion

	/**
	 * Custom form validation of onEvent and onStart events.
	 * @param Form $form
	 */
	public function onValidateForm(Form $form) {
		$values = $form->getValues();

		foreach (array('onEvent', 'onStart', 'onFlow', 'onFinish', 'onCancel') as $event) {
			$this->compiledEvents[$event] = $values[$event . 'Raw'] ? $this->compileJavaScript($event, $values[$event . 'Raw']) : NULL;
		}
	}
}