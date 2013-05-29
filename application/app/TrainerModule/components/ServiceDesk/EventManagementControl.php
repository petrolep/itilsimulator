<?php
/**
 * EventManagement.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:38
 */

namespace ITILSimulator\Trainer\Components\ServiceDesk;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Trainer\Components\TableListControl;
use ITILSimulator\Trainer\Components\TrainerControl;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Paginator;

/**
 * Event management control in Service Desk
 * @package ITILSimulator\Trainer\Components\ServiceDesk
 */
class EventManagementControl extends TableListControl
{
	#region "Properties"

	/** @var ArtifactService */
	protected $artifactsService;

	/** @var OperationEvent */
	protected $event;

	#endregion

	#region "Override"

	public function __construct($parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->invalidateList();
	}

	/**
	 * Count existing events based on current filter.
	 * @return mixed
	 */
	public function getTotalItemsCount() {
		return $this->artifactsService->countAvailableEvents($this->scenarioStep->getId(), $this->getFilter());
	}

	/**
	 * Refresh list of events
	 */
	public function invalidateList() {
		$this->invalidateControl('eventsPanel');
		$this->invalidateControl('eventsPaginator');
	}

	protected function getFilter()
	{
		$filter = new EntityFilter();
		if (isset($this->filter['source']) && $this->filter['source']) {
			$filter->addLike('source', $this->filter['source']);
		}
		if (isset($this->filter['status']) && $this->filter['status']) {
			$filter->addWhere('status', $this->filter['status']);
		}

		return $filter;
	}

	protected function createFilterForm(Form $form) {
		$form->addSelect('status', 'Status', array('' => 'status', '1' => 'new', '2' => 'archived'));
		$form->addText('source', 'Source');
	}

	#endregion

	/**
	 * Load event
	 * @param int $id Event ID
	 * @throws \InvalidArgumentException
	 */
	protected function loadEvent($id) {
		$this->event = $this->artifactsService->getEvent($id);
		$this->template->event = $this->event;

		if (!$this->event || $this->event->getTrainingStepId() != $this->scenarioStep->getTrainingStepId()) {
			throw new \InvalidArgumentException('Invalid event ID #' . $id);
		}
	}

	/**
	 * Display event detail
	 * @param int $id Event ID
	 */
	public function handleDetail($id) {
		$this->loadEvent($id);
		$this->invalidateControl('detailPanel');
	}

	/**
	 * Archive event
	 * @param int $id Event ID
	 */
	public function handleArchive($id) {
		$this->loadEvent($id);
		$event = $this->artifactsService->getEvent($id);
		$this->artifactsService->archiveEvent($event, $this->scenarioStep->getTrainingStep());
		$this->invalidateControl('eventsPanel');
		$this->invalidateControl('detailPanelInner');
	}

	/**
	 * Render control
	 */
	public function render()
	{
		$this->createPaginator();
		$template = $this->getCustomTemplate(__FILE__);
		$template->events = $this->getEvents();
		$template->render();
	}

	protected function getEvents() {
		/** @var Paginator $x */
		$x = $this->paginator->getPaginator();

		return $this->artifactsService->getAvailableEvents($this->scenarioStep->getId(), $x->itemsPerPage, $x->offset, $this->getFilter());
	}

	#region "Get & set"

	/**
	 * @param \ITILSimulator\Services\ArtifactService $artifactsService
	 */
	public function setArtifactsService($artifactsService)
	{
		$this->artifactsService = $artifactsService;
	}

	/**
	 * @return \ITILSimulator\Services\ArtifactService
	 */
	public function getArtifactsService()
	{
		return $this->artifactsService;
	}

	#endregion
}