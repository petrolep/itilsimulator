<?php
/**
 * FlowActivityForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 11:27
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Entities\Workflow\Activities\IncidentActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Training\PriorityEnum;
use Nette\Forms\Form;

/**
 * Incident activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class IncidentActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return IncidentActivity
	 */
	public function getWorkflowActivity()
	{
		$values = $this->getValues();
		if (!$this->workflowActivity)
			$this->workflowActivity = new IncidentActivity();

		foreach($values as $key => $val) {
			$this->workflowActivity->$key = $val;
		}

		// set category
		if ($values['category']) {
			$selectedCategory = (int)$values['category'];
			foreach ($this->availableCategories as $category) {
				if ($category->getId() == $selectedCategory) {
					$this->workflowActivity->setCategory($category);
					break;
				}
			}
		} else {
			$this->workflowActivity->setCategory(NULL);
		}

		$this->workflowActivity->setOnEvent($this->compiledEvents['onEvent']);

		return $this->workflowActivity;
	}

	/**
	 * Set default form values.
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity)
	{
		if ($workflowActivity instanceof IncidentActivity) {
			FormHelper::setDefaultValues($this, $workflowActivity);
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('referenceNumber', 'Reference number:');
		$this->addSelect('priority', 'Priority:', PriorityEnum::getOptions());
		$this->addSelect('urgency', 'Urgency:', PriorityEnum::getOptions());
		$this->addSelect('impact', 'Impact:', PriorityEnum::getOptions());
		$this->addTextArea('symptoms', 'Symptoms:', 60, 8)
			->addRule(Form::FILLED, 'Symptoms are required.');

		$this->addText('timeToResponse', 'TT response:')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'TT response must be a valid number.');

		$this->addText('timeToResolve', 'TT resolve:')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'TT resolve must be a valid number.');

		$this->addCheckbox('isMajor', 'Is major incident');
		$this->addCheckbox('canBeEscalated', 'Can be escalated');

		$this->addSelect('serviceDeskLevel', 'SD level:', array('1' => '1st level', '2' => '2nd level'));
		$this->addSelect('category', 'Category:', array('' => $this->noCategoryText) + $this->getCategoriesOptions());

		$this->addTextArea('onEventRaw', 'onEvent:', 60, 4);

		$this->addSubmit('save', 'Save incident');

		$this->onValidate[] = $this->onValidateForm;
	}

	#endregion

	/**
	 * Populate form with available operation categories.
	 * @param $availableCategories
	 */
	public function setAvailableCategories($availableCategories)
	{
		parent::setAvailableCategories($availableCategories);

		$this['category']->setItems(array('' => $this->noCategoryText) + $this->getCategoriesOptions());
	}

	/**
	 * Custom form validation of onEvent event.
	 * @param Form $form
	 */
	public function onValidateForm(Form $form)
	{
		$values = $form->getValues();

		$this->compiledEvents['onEvent'] = $values['onEventRaw'] ? $this->compileJavaScript('onEvent', $values['onEventRaw']) : NULL;
	}
}