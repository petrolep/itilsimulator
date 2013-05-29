<?php
/**
 * EventManagement.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:38
 */

namespace ITILSimulator\Trainer\Components\ServiceDesk;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Components\TableListControl;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Control displaying existing known issues (errors).
 * @package ITILSimulator\Trainer\Components\ServiceDesk
 */
class KnownIssuesControl extends TableListControl
{
	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	/** @var Training */
	protected $training;

	/** @var KnownIssue */
	protected $knownIssue;

	#endregion

	#region "Override"

	/**
	 * Count existing records
	 * @return int
	 */
	public function getTotalItemsCount() {
		return count($this->getKnownIssues());
	}

	/**
	 * Refresh list of issues
	 */
	public function invalidateList() {
		$this->invalidateControl('issuesPanel');
	}

	protected function getFilter()
	{
		$filter = new EntityFilter();
		if (isset($this->filter['keywords'])) {
			$filter->addLike('keywords', $this->filter['keywords']);
		}
		if (isset($this->filter['name'])) {
			$filter->addLike('name', $this->filter['name']);
		}

		return $filter;
	}

	protected function createFilterForm(Form $form) {
		$form->addText('name', 'Name');
		$form->addText('keyword', 'Keyword');
	}

	#endregion

	protected function loadKnownIssue($id) {
		$this->knownIssue = $this->trainingService->getKnownIssue($id);
		$this->template->knownIssue = $this->knownIssue;

		if (!$this->knownIssue) {
			throw new BadRequestException('Invalid known issue ID #' . $id);
		}
	}

	/**
	 * Display list of existing known errors
	 */
	public function handleDefault()
	{
		$this->invalidateList();
	}

	/**
	 * Display detail of an existing error
	 * @param int $id
	 */
	public function handleDetail($id) {
		$this->loadKnownIssue($id);
		$this->invalidateControl('detailPanel');
	}

	/**
	 * Render control
	 */
	public function render()
	{
		$this->createPaginator();
		$template = $this->getCustomTemplate(__FILE__);
		$template->knownIssues = $this->getKnownIssues();
		$template->render();
	}

	protected function getKnownIssues() {
		return $this->trainingService->getAvailableKnownIssues($this->training->getId(), $this->getFilter());
	}

	#region "Get & set"

	/**
	 * @param \ITILSimulator\Services\TrainingService $trainingService
	 */
	public function setTrainingService($trainingService)
	{
		$this->trainingService = $trainingService;
	}

	/**
	 * @return \ITILSimulator\Services\TrainingService
	 */
	public function getTrainingService()
	{
		return $this->trainingService;
	}

	/**
	 * @param \ITILSimulator\Entities\Training\Training $training
	 */
	public function setTraining($training)
	{
		$this->training = $training;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	#endregion

}