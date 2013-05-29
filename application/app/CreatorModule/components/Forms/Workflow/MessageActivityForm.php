<?php
/**
 * ActivityMessageForm.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 13:21
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;

use ITILSimulator\Entities\Workflow\Activities\MessageActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\UI\MessageTypeEnum;
use Nette\Application\UI\Form;

/**
 * Message activity configuration form.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
class MessageActivityForm extends WorkflowActivityForm
{
	#region "Override"

	/**
	 * Return created WF activity
	 * @return MessageActivity
	 */
	public function getWorkflowActivity()
	{
		if (!$this->workflowActivity)
			$this->workflowActivity = new MessageActivity();

		$this->workflowActivity->setWorkflow($this->getWorkflow());
		$this->workflowActivity->setTitle($this['title']->getValue());
		$this->workflowActivity->setDescription($this['description']->getValue());
		$this->workflowActivity->setType($this['type']->getValue());

		return $this->workflowActivity;
	}

	/**
	 * Set default form values.
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setDefaultValues(WorkflowActivity $workflowActivity) {
		if ($workflowActivity instanceof MessageActivity) {
			/** @var $entity MessageActivity */
			$entity = $workflowActivity;
			$this->setDefaults(array(
				'title' => $entity->getTitle(),
				'description' => $entity->getDescription(),
				'type' => $entity->getType()
			));
		}
	}

	/**
	 * Create custom activity form
	 */
	public function setupControl()
	{
		$this->addText('title', 'Title:')
			->addRule(Form::FILLED, 'Title is required.')
			->getControlPrototype()->addClass('prologue');
		$this->addSelect('type', 'Message type:', MessageTypeEnum::getOptions());
		$this->addTextArea('description', 'Message:', 80, 15);
		$this->addSubmit('save', 'Save activity');
	}

	#endregion
}