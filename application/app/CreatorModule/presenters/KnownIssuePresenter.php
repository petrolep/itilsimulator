<?php
/**
 * KnownIssuePresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 28.4.13 22:53
 */

namespace CreatorModule;

use ITILSimulator\Base\FormHelper;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Presenter for managing known issues (errors).
 * @package CreatorModule
 */
class KnownIssuePresenter extends CreatorPresenter
{
	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	/** @var ArtifactService */
	protected $artifactsService;

	/** @var Training */
	protected $training;

	/** @var KnownIssue */
	protected $knownIssue;

	/** @var OperationCategory[] */
	private $operationalCategories;

	#endregion

	#region "Lifecycle methods"

	public function startup() {
		parent::startup();

		if ($this->isAjax()) {
			$this->setLayout('empty');
		}
	}

	public function inject(TrainingService $trainingService, ArtifactService $artifactsService) {
		$this->trainingService = $trainingService;
		$this->artifactsService = $artifactsService;
	}

	#endregion

	#region "New known issue"

	/**
	 * Add new known error
	 * @param int $id ID of training
	 */
	public function actionNew($id) {
		$this->training = $this->template->training = $this->getTraining($id);

		if ($this['issueForm']->isSubmitted()) {
			$this->invalidateControl('issueForm');
		}
	}

	#endregion

	#region "Edit known issue"

	/**
	 * Edit existing known error
	 * @param int $id ID of known error
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionEdit($id) {
		$this->knownIssue = $this->trainingService->getKnownIssue($id);
		if (!$this->knownIssue)
			throw new BadRequestException('Invalid known issue ID.');

		$this->training = $this->template->training = $this->knownIssue->getTraining();
		$this->template->issue = $this->knownIssue;

		if (!$this['issueForm']->isSubmitted()) {
			FormHelper::setDefaultValues($this['issueForm'], $this->knownIssue);
			$this['issueForm']->setDefaults(array('category' => $this->knownIssue->getCategoryId()));

		} else {
			$this->invalidateControl('issueForm');
		}
	}

	#endregion

	#region "Known issue form component"

	/**
	 * Create known issue form.
	 * @return Form
	 */
	protected function createComponentIssueForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Name:')
			->addRule(Form::FILLED, 'Issue name is required.')
			->getLabelPrototype()->addClass('required');

		$form->addText('code', 'Code:')
			->addRule(Form::FILLED, 'Issue code is required.')
			->getLabelPrototype()->addClass('required');

		$form->addText('keywords', 'Keywords:');
		$form->addTextArea('description', 'Description:', 60, 5);

		$form->addTextArea('workaround', 'Workaround description:', 60, 2)
			->addRule(Form::MAX_LENGTH, 'Workaround description can be up to %d characters long.', 255);
		$form->addText('workaroundCost', 'Workaround cost:')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Workaround cost must be a valid number.');

		$form->addTextArea('fix', 'Fix description:', 60, 2)
			->addRule(Form::MAX_LENGTH, 'Fix description can be up to %d characters long.', 255);
		$form->addText('fixCost', 'Fix cost:')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Fix cost must be a valid number.');

		$form->addSelect('category', 'Category:', $this->getOperationCategories(true));

		$form->addSubmit('save', 'Save issue');

		$form->onSuccess[] = callback($this, 'onIssueFormSuccess');

		return $form;
	}

	/**
	 * Save known error
	 * @param Form $form
	 */
	public function onIssueFormSuccess(Form $form) {
		$values = $form->getValues();

		$knownIssue = $this->knownIssue ?: new KnownIssue();
		FormHelper::updateValues($knownIssue, $form->getValues());
		$knownIssue->setTraining($this->training);

		$categories = $this->getOperationCategories();
		$knownIssue->setCategory($categories[$values['category']]);

		$this->trainingService->updateKnownIssue($knownIssue);

		$this->flashInfoMessage('Known error %s was saved.', $knownIssue->getName());

		$this->template->isSaved = true;

		$this->redirect('training:detail', array('id' => $this->training->getId()));
	}

	#endregion

	#region "Helpers"

	/**
	 * @param int $id training ID
	 * @return Training|null
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function getTraining($id) {
		$training = $this->trainingService->getTrainingByUser($this->getUserIdentity(), $id);
		if (!$training) {
			throw new BadRequestException('Requested training is not available.');
		}

		return $training;
	}

	/**
	 * Load operation categories.
	 * @param bool $asKeyValueArray Return result as associative array (key = ID, value = category name)
	 * @return OperationCategory[]
	 */
	protected function getOperationCategories($asKeyValueArray = FALSE)
	{
		if (!$this->operationalCategories) {
			$this->operationalCategories = $this->artifactsService->getAvailableCategories($this->training->getId());
		}

		/** @var $categories OperationCategory[] */
		$categories = array();
		foreach ($this->operationalCategories as $category) {
			$categories[$category->getId()] = $asKeyValueArray ? $category->getName() : $category;
		}

		return $categories;
	}

	#endregion
}