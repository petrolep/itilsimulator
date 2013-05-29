<?php
/**
 * TrainingPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 15:16
 */

namespace CreatorModule;


use ITILSimulator\Base\FormHelper;
use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Training\InputOutput;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Services\WorkflowService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Presenter for managing trainings.
 * @package CreatorModule
 */
class TrainingPresenter extends CreatorPresenter
{
	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	/** @var ArtifactService */
	protected $artifactsService;

	/** @var WorkflowService */
	protected $workflowService;

	/** @var Training */
	protected $selectedTraining = null;

	/** @var Scenario */
	protected $selectedScenario = null;

	#endregion

	#region "Lifecycle methods"

	/**
	 * @param TrainingService $trainingService
	 * @param ArtifactService $artifactService
	 * @param WorkflowService $workflowService
	 */
	public function inject(TrainingService $trainingService, ArtifactService $artifactService, WorkflowService $workflowService) {
		$this->trainingService = $trainingService;
		$this->artifactsService = $artifactService;
		$this->workflowService = $workflowService;
	}

	public function beforeRender() {
		parent::beforeRender();

		$this->template->training = $this->selectedTraining;
		$this->template->scenario = $this->selectedScenario;
	}

	#endregion

	#region "List"

	/**
	 * List of my trainings
	 * @param int $trainingId
	 */
	public function actionList($trainingId = 0) {
		$this->template->trainings = $this->trainingService->getTrainingsByUser($this->user->getId());
	}

	public function actionDefault() {
		$this->redirect('list');
	}

	#endregion

	#region "Trainings"

	/**
	 * Detail of selected training
	 * @param int $id Training ID
	 */
	public function actionDetail($id) {
		$this->selectedTraining = $this->getTraining($id);

		if ($this['inputOutputForm']->isSubmitted()) {
			$this->invalidateControl('inputsOutputs');
		}

		if ($this['operationCategoryForm']->isSubmitted()) {
			$this->invalidateControl('operationCategories');
		}
	}

	/**
	 * New training
	 */
	public function actionNew() {
		if ($this->isAjax()) {
			$this->setLayout('empty');
		}

		if ($this['trainingForm']->isSubmitted()) {
			$this->invalidateControl('trainingForm');
		}
	}

	/**
	 * Edit an existing training
	 * @param int $id Training ID
	 */
	public function actionEdit($id) {
		$training = $this->getTraining($id);

		FormHelper::setDefaultValues($this['trainingForm'], $training);

		$this->selectedTraining = $training;

		if ($this['trainingForm']->isSubmitted()) {
			$this->invalidateControl('trainingForm');
		}

		if ($this->isAjax()) {
			$this->setLayout('empty');
		}
	}

	/**
	 * Delete an existing training
	 * @param int $id Training ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteTraining($id) {
		$training = $this->getTraining($id);
		if (!$training) {
			throw new BadRequestException('Invalid training ID.');
		}

		$this->trainingService->deleteTraining($training);

		$this->redirect('default');
	}


	#region "Publishing training"

	/**
	 * Set selected training as published/unpublished.
	 * @param int $id Training ID
	 * @param bool $isPublished TRUE to publish the training, FALSE to unpublish it.
	 */
	public function handlePublish($id, $isPublished) {
		$selectedTraining = $this->getTraining($id);
		$this->trainingService->publishTraining($selectedTraining, $isPublished);

		$this->invalidateAjaxRegions($id);
	}

	protected function invalidateAjaxRegions($id) {
		if ($this->isAjax() && $this->getParameter('list')) {
			// reload list
			$this->invalidateControl('trainingsList');

		} else {
			// reload detail
			$this->redirect('detail', array('id' => $id));
		}
	}

	#endregion

	#region "Public / private training"

	/**
	 * Set selected training as public/private.
	 * @param int $id Training ID
	 * @param bool $isPublic TRUE to mark the training as public, FALSE as private.
	 */
	public function handleVisibility($id, $isPublic) {
		$selectedTraining = $this->getTraining($id);
		$this->trainingService->publicizeTraining($selectedTraining, $isPublic);

		$this->invalidateAjaxRegions($id);
	}

	#endregion

	#region "Assigning services"

	/**
	 * Assign service to selected scenario.
	 * @param int $scenarioId Scenario ID
	 * @param int $serviceId Service ID
	 * @param bool $isDesignService TRUE if the service is the designing service in Design scenario, FALSE otherwise
	 * @throws \Nette\Application\BadRequestException
	 */
	private function assignService($scenarioId, $serviceId, $isDesignService) {
		$scenario = $this->trainingService->getScenario($scenarioId);
		if (!$scenario) {
			throw new BadRequestException('Invalid scenario ID.');
		}

		$service = $this->trainingService->getService($serviceId);
		if (!$service) {
			throw new BadRequestException('Invalid service ID.');
		}

		if ($isDesignService) {
			$scenario->setDesignService($service);
		} else {
			$scenario->assignService($service);
		}

		$this->trainingService->updateScenario($scenario);

		if ($this->isAjax()) {
			$this->invalidateControl('scenariosList');

		} else {
			$this->redirect('detail', array('id' => $scenario->getTraining()->getId()));
		}
	}

	/**
	 * Assign service to scenario
	 * @param int $scenarioId
	 * @param int $serviceId
	 */
	public function handleAssignService($scenarioId, $serviceId) {
		$this->assignService($scenarioId, $serviceId, false);
	}

	/**
	 * Assign design service to scenario
	 * @param int $scenarioId
	 * @param int $serviceId
	 */
	public function handleAssignDesignService($scenarioId, $serviceId) {
		$this->assignService($scenarioId, $serviceId, true);
	}

	/**
	 * Unassign service from scenario
	 * @param int $scenarioId
	 * @param int $serviceId
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleUnassignService($scenarioId, $serviceId) {
		$scenario = $this->trainingService->getScenario($scenarioId);
		if (!$scenario) {
			throw new BadRequestException('Invalid scenario ID.');
		}

		$service = $this->trainingService->getService($serviceId);
		if (!$service) {
			throw new BadRequestException('Invalid service ID.');
		}

		$scenario->unassignService($service);
		$this->trainingService->updateScenario($scenario);

		if ($this->isAjax()) {
			$this->invalidateControl('scenariosList');

		} else {
			$this->redirect('detail', array('id' => $scenario->getTraining()->getId()));
		}
	}

	#endregion

	/**
	 * Delete an existing operation category
	 * @param int $categoryId
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteOperationCategory($categoryId) {
		$category = $this->artifactsService->getCategory($categoryId);
		if (!$category || $category->getTrainingId() != $this->selectedTraining->getId()) {
			throw new BadRequestException('Invalid category ID');
		}

		$this->artifactsService->deleteCategory($category);
		$this->artifactsService->commitChanges();

		$this->flashInfoMessage('Category %s was deleted.', $category->getName());

		$this->invalidateControl('operationCategories');
		$this->invalidateControl('flashes');

		if (!$this->isAjax()) {
			$this->redirect('detail', array('id' => $this->selectedTraining->getId()));
		}
	}

	/**
	 * Delete an existing input/output
	 * @param int $ioId
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteInputOutput($ioId) {
		$io = $this->trainingService->getInputOutput($ioId);
		if (!$io || $io->getTrainingId() != $this->selectedTraining->getId()) {
			throw new BadRequestException('Invalid input/output ID');
		}

		$this->trainingService->deleteInputOutput($io);
		$this->trainingService->commitChanges();

		$this->flashInfoMessage('Input/output %s was deleted.', $io->getName());

		$this->invalidateControl('inputsOutputs');
		$this->invalidateControl('flashes');

		if (!$this->isAjax()) {
			$this->redirect('detail', array('id' => $this->selectedTraining->getId()));
		}
	}

	/**
	 * Delete an existing workflow.
	 * @param int $workflowId
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteWorkflow($workflowId) {
		$workflow = $this->workflowService->getWorkflow($workflowId);
		if (!$workflow || $workflow->getTrainingId() != $this->selectedTraining->getId()) {
			throw new BadRequestException;
		}

		$this->workflowService->deleteWorkflow($workflow);

		$this->flashInfoMessage('Workflow %s was deleted.', $workflow->getName());

		$this->invalidateControl('scenariosList');

		if (!$this->isAjax()) {
			$this->redirect('detail', array('id' => $this->selectedTraining->getId()));
		}
	}

	public function handleDeleteKnownIssue($issueId) {
		$knownIssue = $this->trainingService->getKnownIssue($issueId);
		if (!$knownIssue || $knownIssue->getTrainingId() != $this->selectedTraining->getId()) {
			throw new BadRequestException;
		}

		$this->trainingService->deleteKnownIssue($knownIssue);

		$this->flashInfoMessage('Known error %s was deleted.', $knownIssue->getName());

		$this->invalidateControl('knownIssues');
		$this->invalidateControl('flashes');

		if (!$this->isAjax()) {
			$this->redirect('detail', array('id' => $this->selectedTraining->getId()));
		}
	}

	#endregion

	#region "Scenarios"

	/**
	 * Create new scenario
	 * @param int $trainingId Training ID
	 */
	public function actionNewScenario($trainingId) {
		$this->selectedTraining = $this->getTraining($trainingId);

		if ($this->isAjax()) {
			$this->setLayout('empty');
		}
	}

	/**
	 * Edit an existing scenario
	 * @param int $id Scenario ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionEditScenario($id) {
		$scenario = $this->trainingService->getScenario($id);
		if (!$scenario) {
			throw new BadRequestException('Invalid scenario ID.');
		}

		$this->selectedScenario = $scenario;
		$this->selectedTraining = $scenario->getTraining();

		FormHelper::setDefaultValues($this['scenarioForm'], $scenario);

		$this['scenarioForm']['type']->setDisabled(true);

		if ($this->isAjax()) {
			$this->setLayout('empty');

			if ($this['scenarioForm']->isSubmitted()) {
				$this->invalidateControl('ajaxDialog');
			}
		}
	}

	/**
	 * Delete an existing scenario
	 * @param int $id Scenario ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteScenario($id) {
		$scenario = $this->trainingService->getScenario($id);
		if (!$scenario) {
			throw new BadRequestException('Invalid scenario ID.');
		}

		if ($this->getUserId() != $scenario->getCreatorUserId()) {
			// can not access
			throw new BadRequestException('Invalid scenario ID.');
		}

		$trainingId = $scenario->getTrainingId();

		$this->trainingService->deleteScenario($scenario);

		$this->redirect('detail', array('id' => $trainingId));
	}

	#endregion

	#region "Helpers"

	/**
	 * Load training and check access rights
	 * @param int $id
	 * @return Training|null
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function getTraining($id) {
		$training = $this->trainingService->getTrainingByUser($this->getUserIdentity(), $id);
		if (!$training) {
			throw new BadRequestException('Invalid training ID.');
		}

		return $training;
	}

	#endregion

	#region "Components"

	#region "Training form"

	/**
	 * Create training form component
	 * @return Form
	 */
	protected function createComponentTrainingForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Training name:')
			->addRule(Form::FILLED, 'Training name is required.')
			->getLabelPrototype()->addClass('required');

		$form->addTextArea('shortDescription', 'Short description:', 40, 3)
			->addRule(Form::FILLED, 'Short description is required.')
			->addRule(Form::MAX_LENGTH, 'Short description can be 255 length long.', 255)
			->getLabelPrototype()->addClass('required');

		$form->addTextArea('description', 'Description:');
		$form->addCheckbox('isPublic', 'Training is available to public users');
		$form->addCheckbox('isPublished', 'Publish training');

		$form->addSubmit('submit', 'Save training')
			->onClick[] = callback($this, 'onTrainingFormSuccess');

		$cancel = $form->addSubmit('cancel', 'Cancel');
		$cancel->setValidationScope(false);
		$cancel->onClick[] = callback($this, 'onScenarioFormCancel');
		$cancel->getControlPrototype()->addClass('cancel');

		return $form;
	}

	/**
	 * Save training form
	 * @param $button
	 */
	public function onTrainingFormSuccess($button)
	{
		$values = $this['trainingForm']->getValues();

		$training = ($this->selectedTraining) ? $this->selectedTraining : new Training();
		$training->setName($values['name']);
		$training->setShortDescription($values['shortDescription']);
		$training->setDescription($values['description']);
		$training->setIsPublic($values['isPublic']);
		$training->setIsPublished($values['isPublished']);
		$training->setUser($this->getDBUser());

		$this->trainingService->updateTraining($training);
		$this->trainingService->commitChanges();

		$this->flashInfoMessage('Training %s was saved.', $training->getName());

		if ($this->isAjax()) {
			$this->template->isSaved = true;
			$this->invalidateControl('trainingForm');

		} else {
			$this->redirect('detail', array('id' => $training->getId()));
		}
	}

	#endregion

	#region "Scenario form"

	/**
	 * Create scenario form component
	 * @return Form
	 */
	protected function createComponentScenarioForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Scenario name:')
			->addRule(Form::FILLED, 'Scenario name is required.')
			->getLabelPrototype()->addClass('required');

		$form->addTextArea('description', 'Scenario description:');
		$form->addTextArea('detailDescription', 'Detail task description:');

		$form->addText('initialBudget', sprintf($this->translator->translate('Initial budget (%s):'), TemplateHelpers::emptyCurrency()))
			->setType('number')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Budget must be a valid number.');

		$form->addRadioList('type', 'Scenario type:', array('operation' => 'Service operation', 'design' => 'Service design'))
			->addRule(Form::FILLED, 'Scenario type is required')
			->setDefaultValue('operation');

		$form->addSubmit('submit', 'Save scenario')
			->onClick[] = callback($this, 'onScenarioFormSuccess');

		$cancel = $form->addSubmit('cancel', 'Cancel');
		$cancel->setValidationScope(false);
		$cancel->onClick[] = callback($this, 'onScenarioFormCancel');
		$cancel->getControlPrototype()->addClass('cancel');

		if ($this->isAjax()) {
			$form->getElementPrototype()->addClass('ajax');
		}

		return $form;
	}

	/**
	 * Save scenario form
	 * @param $button
	 */
	public function onScenarioFormSuccess($button) {
		$scenario = $this->selectedScenario ? $this->selectedScenario : new Scenario();
		$scenario->setTraining($this->selectedTraining);
		FormHelper::updateValues($scenario, $this['scenarioForm']->getValues());

		$this->trainingService->updateScenario($scenario);

		$this->flashInfoMessage('Scenario %s was saved.', $scenario->getName());
		$this->redirect('detail', $scenario->getTrainingId());
	}

	/**
	 * Cancel scenario form
	 */
	public function onScenarioFormCancel() {
		if ($this->selectedTraining)
			$this->redirect('detail', array('id' => $this->selectedTraining->getId()));

		$this->redirect('list');
	}

	#endregion

	#region "Operation category form"

	/**
	 * Create operation category form component
	 * @return Form
	 */
	protected function createComponentOperationCategoryForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Category name: ')
			->addRule(Form::FILLED, 'Category name is required.');

		$form->addSubmit('save', 'Create category');

		$form->onSuccess[] = callback($this, 'onOperationCategoryFormSuccess');

		return $form;
	}

	public function onOperationCategoryFormSuccess(Form $form) {
		$values = $form->getValues();

		$category = new OperationCategory();
		$category->setName($values['name']);
		$category->setTraining($this->selectedTraining);

		$this->artifactsService->saveCategory($category);
		$this->artifactsService->commitChanges();

		$this->flashInfoMessage('Category %s was saved.', $category->getName());

		$this->invalidateControl('operationCategories');
		$this->invalidateControl('flashes');
	}

	#endregion

	#region "Input/output form"

	/**
	 * Create input/output form component
	 * @return Form
	 */
	protected function createComponentInputOutputForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Name: ')
			->addRule(Form::FILLED, 'Input/output name is required.');

		$form->addText('code', 'Code: ')
			->addRule(Form::FILLED, 'Input/output code is required.');

		$form->addSubmit('save', 'Create IO');

		$form->onSuccess[] = callback($this, 'onInputOutputFormSuccess');

		return $form;
	}

	/**
	 * Save input/output form
	 * @param Form $form
	 */
	public function onInputOutputFormSuccess(Form $form) {
		$values = $form->getValues();

		$io = new InputOutput();
		$io->setName($values['name']);
		$io->setCode($values['code']);

		$io->setTraining($this->selectedTraining);

		$this->trainingService->updateInputOutput($io);

		$this->trainingService->commitChanges();

		$this->flashInfoMessage('Input/output was updated.');

		$this->invalidateControl('inputsOutputs');
		$this->invalidateControl('flashes');
	}

	#endregion

	#endregion
}