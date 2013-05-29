<?php
/**
 * FlowActivityForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 11:27
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Workflow\Activities\ProblemActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Training\PriorityEnum;
use Nette\Forms\Form;

/**
 * Problem activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class ProblemActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return ProblemActivity
	 */
	public function getWorkflowActivity()
	{
		$values = $this->getValues();
		if (!$this->workflowActivity)
			$this->workflowActivity = new ProblemActivity();

		FormHelper::updateValues($this->workflowActivity, $this->getValues());

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

		return $this->workflowActivity;
	}

	/**
	 * Set default form values.
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity)
	{
		if ($workflowActivity instanceof ProblemActivity) {
			FormHelper::setDefaultValues($this, $workflowActivity);
			if ($workflowActivity->getCategory()) {
				$this->setDefaults(array('category' => $workflowActivity->getCategory()->getId()));
			}
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('referenceNumber', 'Reference number:');
		$this->addSelect('priority', 'Priority:', PriorityEnum::getOptions());
		$this->addTextArea('symptoms', 'Symptoms:', 40, 8)
			->addRule(Form::FILLED, 'Symptoms are required.');
		$this->addText('problemOwner', 'Problem owner:');
		$this->addSelect('status', 'State:', array(
			OperationProblem::STATUS_NEW => 'new',
			OperationProblem::STATUS_INVESTIGATED => 'investigated',
			OperationProblem::STATUS_RESOLVED => 'resolved',
			OperationProblem::STATUS_CLOSED => 'closed'
		));

		$this->addSelect('category', 'Category:', array('' => $this->noCategoryText) + $this->getCategoriesOptions());

		$this->addTextArea('onEventRaw', 'onEvent:');

		$this->addSubmit('save', 'Save problem');

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