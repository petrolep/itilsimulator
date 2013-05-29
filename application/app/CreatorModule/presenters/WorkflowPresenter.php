<?php
/**
 * WorkflowPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 11:34
 */

namespace CreatorModule;

use ITILSimulator\Base\Position;
use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Creator\Components\Forms\Workflow\WorkflowActivityForm;
use ITILSimulator\Creator\Components\Forms\Workflow\WorkflowActivityFormControl;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\Workflow\Activities\CustomActivity;
use ITILSimulator\Entities\Workflow\Activities\EvaluationActivity;
use ITILSimulator\Entities\Workflow\Activities\FinishActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\Activities\IncidentActivity;
use ITILSimulator\Entities\Workflow\Activities\MessageActivity;
use ITILSimulator\Entities\Workflow\Activities\ProblemActivity;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Entities\Workflow\ActivityMetadata;
use ITILSimulator\Entities\Workflow\Workflow;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Workflow\WorkflowFactory;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Services\WorkflowService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Templating\Helpers;
use Nette\Utils\Html;
use Nette\Utils\Strings;

/**
 * Presenter for managing workflows
 * @package CreatorModule
 */
class WorkflowPresenter extends CreatorPresenter
{
	#region "Properties"

	/** @var WorkflowService */
	protected $workflowService;

	/** @var TrainingService */
	protected $trainingService;

	/** @var ArtifactService */
	protected $artifactsService;

	/** @var Workflow */
	protected $workflow;

	/** @var \ITILSimulator\Entities\Workflow\WorkflowActivity */
	protected $activity;

	public function inject(WorkflowService $workflowService, TrainingService $trainingService,
	                       ArtifactService $artifactsService) {
		$this->workflowService = $workflowService;
		$this->trainingService = $trainingService;
		$this->artifactsService = $artifactsService;
	}

	#endregion

	#region "Designer"

	/**
	 * Prepare workflow designer
	 * @param int $id Workflow ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionDesigner($id) {
		if ($id) {
			$this->loadWorkflow($id);

		} else {
			if ($scenarioId = $this->getParameter('new')) {
				$scenario = $this->trainingService->getScenario($scenarioId);
				if (!$scenario || $scenario->getCreatorUserId() != $this->getUserId())
					throw new BadRequestException('Invalid scenario.');

				$workflowName = $this->translator->translate(sprintf($this->itilConfigurator->getDefaultWorkflowName(), date('d.m.Y')));

				$factory = new WorkflowFactory();
				$this->workflow = $factory->makeWorkflow($workflowName, $scenario);

				// persist workflow
				$this->workflowService->updateWorkflow($this->workflow);
				foreach($this->workflow->getWorkflowActivities() as $activity) {
					$this->workflowService->updateWorkflowActivity($activity);
				}

				$this->workflowService->commitChanges();

				$this->redirect('designer', array('id' => $this->workflow->getId()));

			} else {
				throw new BadRequestException;
			}
		}
	}

	/**
	 * Render workflow designer
	 */
	public function renderDesigner() {
		/** @var $workflow Workflow */
		$workflow = $this->template->workflow = $this->workflow;

		$connections = array();
		foreach($workflow->getWorkflowActivities() as $activity) {
			foreach ($activity->getNextActivities() as $nextActivity) {
				$connections[] = array('source' => $activity->getId(), 'target' => $nextActivity->getId());
			}
		}

		$this->template->activities = array_filter(
			$workflow->getWorkflowActivities()->toArray(),
			function($activity) {
				return !$activity instanceof FlowActivity;
			}
		);

		$this->template->connections = array_filter(
			$workflow->getWorkflowActivities()->toArray(),
			function($activity) {
				return $activity instanceof FlowActivity;
			}
		);
	}

	/**
	 * Create workflow form
	 * @return Form
	 */
	public function createComponentSaveWorkflowForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$form->addText('name', 'Name:')
			->addRule(Form::FILLED, 'Please enter name of the workflow');
		$form->addHidden('position');
		$form->addHidden('relations');
		$form->addSubmit('cancel', 'Back to training')
			->setValidationScope(false)
			->onClick[] = callback($this, 'onSaveWorkflowFormCancel');
		$form->addSubmit('save', 'Save workflow')
			->onClick[] = callback($this, 'onSaveWorkflowFormSuccess');

		$form['cancel']->getControlPrototype()->addClass('cancel');

		$form->setDefaults(array('name' => $this->workflow->getName()));

		return $form;
	}

	/**
	 * Workflow form saved
	 */
	public function onSaveWorkflowFormSuccess() {
		$values = $this['saveWorkflowForm']->getValues();

		$positionData = json_decode($values['position']);
		if ($positionData && is_array($positionData)) {
			$this->savePositionData($positionData);
		}

		$relationsData = json_decode($values['relations']);
		if($relationsData) {
			$this->saveRelations($relationsData);
		}

		$this->workflow->setName($values['name']);
		$this->workflowService->updateWorkflow($this->workflow);

		$this->flashInfoMessage('Workflow %s was saved.', $this->workflow->getName());
		$this->redirect('this');
	}

	/**
	 * Workflow form cancelled
	 */
	public function onSaveWorkflowFormCancel() {
		$this->redirect('training:detail', array('id' => $this->workflow->getTrainingId()));
	}


	#endregion

	#region "Activities"

	/**
	 * Edit activity based on request from designer
	 * @param int $workflowId
	 * @param int $id Activity ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionEditActivity($workflowId, $id) {
		$this->loadWorkflow($workflowId);

		if (!preg_match('/^[0-9]$/', $id))
			$id = $this->parseActivityID($id);

		$this->activity = $activity = $this->workflowService->getWorkflowActivity($id);

		if (!$activity || $activity->getWorkflowId() != $this->workflow->getId())
			throw new BadRequestException('Activity and workflow does not match.');

		if ($activity instanceof StartActivity || $activity instanceof FinishActivity)
			die('noform');

		$this->template->activity = $activity;
	}

	/**
	 * Create new activity based on request from designer
	 * @param $type
	 * @param $workflowId
	 * @throws \Nette\InvalidArgumentException
	 */
	public function handleCreateActivity($type, $workflowId) {
		$this->loadWorkflow($workflowId);

		/** @var MessageActivity $activity */
		$activity = $this->getNewActivity($type);
		if (!$activity)
			throw new InvalidArgumentException('Invalid activity type ' . $type);

		$activity->setWorkflow($this->workflow);
		$metadata = new ActivityMetadata();
		$metadata->setPosition(new Position($this->getParameter('left') ?: 20, 20));
		$activity->setMetadata($metadata);

		$this->workflowService->updateWorkflowActivity($activity);
		$this->workflowService->commitChanges();

		$result = array(
			'id' => $activity->getId(),
			'htmlid' => $this->generateActivityHTMLId($activity),
			'html' => $this->renderActivityItem($activity)
		);

		$this->jsonResponse($result);
	}

	/**
	 * Delete selected activity based on request from designer
	 * @param $htmlId
	 */
	public function handleDeleteActivity($htmlId) {
		$activityId = $this->parseActivityID($htmlId);
		$activity = $this->workflowService->getWorkflowActivity($activityId);
		if ($activity) {
			if (!$activity instanceof StartActivity) {
				$this->workflowService->deleteWorkflowActivity($activity);

				$this->jsonResponseStatus(true);
			}

		} else {
			$this->jsonResponseStatus(false);
		}
	}

	/**
	 * Create activity form component
	 * @return WorkflowActivityFormControl
	 */
	public function createComponentEditActivityFormControl() {
		$availableCategories = $this->getOperationCategories();

		$control = new WorkflowActivityFormControl();
		$control->setTranslator($this->translator);
		$control->setWorkflow($this->workflow);
		$control->setWorkflowActivity($this->activity);
		$control->setAvailableCategories($availableCategories);
		$control->onSuccess[] = callback($this, 'onEditActivityFormControlSuccess');
		$this->invalidateControl('form');

		return $control;
	}

	/**
	 * Save activity form
	 * @param WorkflowActivityForm $form
	 */
	public function onEditActivityFormControlSuccess(WorkflowActivityForm $form) {
		$activity = $form->getWorkflowActivity();

		$this->workflowService->updateWorkflowActivity($activity);

		$this->flashInfoMessage('Activity was updated.');

		$this->template->isSaved = true;
		$this->template->activityHtmlId = $this->generateActivityHTMLId($activity);
		$this->template->activityDescription = $this->getActivityDescription($activity);
		$this->template->activityId = $activity->getId();
	}

	#endregion

	#region "Helpers"

	/**
	 * Generate HTML activity ID
	 * @param $activity
	 * @return string
	 */
	public function generateActivityHTMLId($activity) {
		if (!$activity instanceof WorkflowActivity)
			return '';

		return sprintf('w%da%d', $this->workflow->getId(), $activity->getId());
	}

	/**
	 * Render activity HTML code.
	 * @param WorkflowActivity $activity
	 * @return string
	 */
	public function renderActivityItem(WorkflowActivity $activity) {
		$title = $this->getActivityTitle($activity);
		$description = $this->getActivityDescription($activity);

		Html::$xhtml = false;
		$result = Html::el('div')
			->addAttributes(array(
				'class' => 'item activity-' . $activity->getStringType(),
				'data-id' => $activity->getId(),
				'id' => $this->generateActivityHTMLId($activity),
				'style' => sprintf('left: %dpx; top: %dpx', $activity->getMetadata()->getPosition()->getX(), $activity->getMetadata()->getPosition()->getY()),
				'title' => $this->translator->translate('Click to edit, drag to change position'),
			));
		$result->create('span class="title"', $title);
		$result->create('span class="description"', $description);
		$result->create('span class="handle"')
			->setTitle($this->translator->translate('Click and drag'));

		if (!$activity instanceof StartActivity)
			$result->create('a href="#" class="remove"')
				->setTitle($this->translator->translate('Delete activity'));

		return $result->render();
	}

	/**
	 * Return new activity based on selected type
	 * @param string $type Selected type (message/evaluation/incident/problem/start/finish)
	 * @return EvaluationActivity|FinishActivity|IncidentActivity|MessageActivity|ProblemActivity|StartActivity
	 */
	protected function getNewActivity($type) {
		switch($type) {
			case 'message':
				return new MessageActivity();

			case 'evaluation':
				return new EvaluationActivity();

			case 'incident':
				return new IncidentActivity();

			case 'problem':
				return new ProblemActivity();

			case 'start':
				return new StartActivity();

			case 'finish':
				return new FinishActivity();

			case 'custom':
				return new CustomActivity();
		}
	}

	/**
	 * Parse activity ID from its HTML representation
	 * @param $htmlId
	 * @return int
	 */
	protected function parseActivityID($htmlId) {
		return intval(str_replace('w' . $this->workflow->getId() . 'a', '', $htmlId));
	}

	/**
	 * Load workflow and check permissions
	 * @param int $id Workflow ID
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function loadWorkflow($id)
	{
		$this->workflow = $this->workflowService->getWorkflow($id);
		if (!$this->workflow || $this->workflow->getCreatorUserId() != $this->getUserId()) {
			throw new InvalidArgumentException('Invalid workflow id.');
		}

		$this->template->workflow = $this->workflow;
	}

	/**
	 * Return localized activity title
	 * @param WorkflowActivity $activity
	 * @return string
	 */
	protected function getActivityTitle(WorkflowActivity $activity) {
		return $this->translator->translate(ucfirst($activity->getStringType()));
	}

	/**
	 * Return activity description
	 * @param WorkflowActivity $activity
	 * @return string
	 */
	protected function getActivityDescription(WorkflowActivity $activity) {
		switch($activity) {
			case ($activity instanceof MessageActivity):
				return Strings::truncate($activity->getTitle(), 50);

			case ($activity instanceof IncidentActivity):
			case ($activity instanceof ProblemActivity):
				return $activity->getReferenceNumber();

			case ($activity instanceof CustomActivity):
			case ($activity instanceof FlowActivity):
				return Strings::truncate($activity->getDescription(), 50);

			case ($activity instanceof EvaluationActivity):
				/** @var $activity EvaluationActivity */
				$points = $activity->getPoints() ? $this->translator->translate('%s points', $activity->getPoints()) : '';
				$money = $activity->getMoney() ? TemplateHelpers::currency($activity->getMoney()) : '';

				if ($points && $money)
					return $points . ', ' . $money;

				return $points ? $points : $money;
		}

		return '';
	}

	/**
	 * Return available operation categories
	 * @return array
	 */
	protected function getOperationCategories()
	{
		return $this->artifactsService->getAvailableCategories($this->activity->getTrainingId());
	}

	/**
	 * Save positions from designer
	 * @param $positionData
	 */
	protected function savePositionData($positionData)
	{
		foreach ($positionData as $activityData) {
			if (!isset($activityData->id) || !isset($activityData->x) || !isset($activityData->y))
				// missing data
				continue;

			$activity = $this->workflowService->getWorkflowActivity($activityData->id);
			if (!$this->isAuthorized($activity))
				// not authorized
				continue;

			// remember position of the activity in the workflow designer
			$metadata = $activity->getMetadata();
			$metadata->setPosition(new Position((int)$activityData->x, (int)$activityData->y));
			$activity->setMetadata($metadata);

			$this->workflowService->updateWorkflowActivity($activity);
		}
	}

	/**
	 * Check if current user is authorized for selected activity
	 * @param WorkflowActivity $activity
	 * @return bool
	 */
	protected function isAuthorized(WorkflowActivity $activity)
	{
		return $activity->getCreatorUserId() == $this->getUserId();
	}

	#endregion

	#region "Workflow activities relations"

	/**
	 * Save relations created with the worklfow designer
	 * @param $relationsData
	 */
	protected function saveRelations($relationsData)
	{
		if (isset($relationsData->create) && is_array($relationsData->create)) {
			foreach ($relationsData->create as $create) {
				$this->createNewRelation($create);
			}
		}
		if (isset($relationsData->delete) && is_array($relationsData->delete)) {
			foreach ($relationsData->delete as $delete) {
				$this->deleteExistingRelation($delete);
			}
		}
		if (isset($relationsData->update) && is_array($relationsData->update)) {
			foreach ($relationsData->update as $update) {
				$this->updateExistingRelation($update);
			}
		}
	}

	/**
	 * Create new relations
	 * @param $create
	 */
	protected function createNewRelation($create)
	{
		$sourceActivityId = $this->parseActivityId($create->source);
		$targetActivityId = $this->parseActivityId($create->target);

		$targetActivity = $this->workflowService->getWorkflowActivity($targetActivityId);
		$sourceActivity = $this->workflowService->getWorkflowActivity($sourceActivityId);

		if ($targetActivity->getWorkflowId() != $sourceActivity->getWorkflowId() || !$this->isAuthorized($targetActivity))
			// not authorized
			return;

		$flowActivity = new FlowActivity();
		$flowActivity->setWorkflow($this->workflow);
		$flowActivity->addNextActivity($targetActivity);

		$this->workflowService->updateWorkflowActivity($flowActivity);

		$sourceActivity->addNextActivity($flowActivity);
		$this->workflowService->updateWorkflowActivity($sourceActivity);
	}

	/**
	 * Delete an existing relation
	 * @param $delete
	 */
	protected function deleteExistingRelation($delete)
	{
		$flowActivity = $this->workflowService->getWorkflowActivity((int)$delete->id);
		if (!$this->isAuthorized($flowActivity))
			// not authorized
			return;

		$this->workflowService->deleteWorkflowActivity($flowActivity);
	}

	/**
	 * Update an existing relation
	 * @param $update
	 */
	protected function updateExistingRelation($update)
	{
		$sourceActivityId = $this->parseActivityId($update->source);
		$targetActivityId = $this->parseActivityId($update->target);

		/** @var FlowActivity $flowActivity */
		$flowActivity = $this->workflowService->getWorkflowActivity((int)$update->id);
		if (!$this->isAuthorized($flowActivity))
			// not authorized
			return;

		if ($flowActivity instanceof FlowActivity) {
			$targetActivity = $this->workflowService->getWorkflowActivity($targetActivityId);
			if (!$this->isAuthorized($targetActivity))
				return;

			// update source
			if ($flowActivity->getSource() && $flowActivity->getSourceId() != $sourceActivityId) {
				// add to new source
				$sourceActivity = $this->workflowService->getWorkflowActivity($sourceActivityId);
				if (!$this->isAuthorized($sourceActivity))
					return;

				$flowActivity->setSource($sourceActivity);

				$this->workflowService->updateWorkflowActivity($sourceActivity);
			}

			// update target
			$flowActivity->setTarget($targetActivity);

			$this->workflowService->updateWorkflowActivity($flowActivity);
		}
	}

	#endregion
}