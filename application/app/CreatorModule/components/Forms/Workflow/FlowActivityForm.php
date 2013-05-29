<?php
/**
 * FlowActivityForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 11:27
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\Application\UI\Form;

/**
 * Flow activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class FlowActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return FlowActivity
	 */
	public function getWorkflowActivity()
	{
		if (!$this->workflowActivity)
			$this->workflowActivity = new FlowActivity();

		foreach ($this->getValues() as $key => $value) {
			$this->workflowActivity->$key = $value;
		}

		$this->workflowActivity->setOnEvent($this->compiledEvents['onEvent']);
		$this->workflowActivity->setOnStart($this->compiledEvents['onStart']);

		return $this->workflowActivity;
	}

	/**
	 * Set default form values
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity)
	{
		if ($workflowActivity instanceof FlowActivity) {
			FormHelper::setDefaultValues($this, $workflowActivity);
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('description', 'Description:');
		$this->addTextArea('onEventRaw', 'onEvent:', 80, 15);
		$this->addTextArea('onStartRaw', 'onStart:', 80, 5);

		$this->addSubmit('save', 'Save flow');

		$this->onValidate[] = $this->onValidateForm;
	}

	#endregion

	/**
	 * Custom form validation of onEvent and onStart events.
	 * @param Form $form
	 */
	public function onValidateForm(Form $form) {
		$values = $form->getValues();

		$this->compiledEvents['onEvent'] = $values['onEventRaw'] ? $this->compileJavaScript('onEvent', $values['onEventRaw']) : NULL;
		$this->compiledEvents['onStart'] = $values['onStartRaw'] ? $this->compileJavaScript('onStart', $values['onStartRaw']) : NULL;
	}
}