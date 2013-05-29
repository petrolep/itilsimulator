<?php
/**
 * TableListControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 27.4.13 10:24
 */

namespace ITILSimulator\Trainer\Components;


use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Trainer\Components\TrainerControl;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;

/**
 * Table list control for displaying table-like data.
 * @package ITILSimulator\Trainer\Components
 */
abstract class TableListControl extends TrainerControl
{
	#region "Properties"

	/** @var ScenarioStep */
	protected $scenarioStep;

	/** @persistent */
	public $itemsPerPage = 10;

	/** @persistent */
	public $filter = array();

	/** @var \VisualPaginator */
	protected $paginator;

	#endregion

	#region "Abstract methods"

	/**
	 * Count available records
	 * @return mixed
	 */
	abstract public function getTotalItemsCount();

	/**
	 * Refresh (invalidate) list
	 * @return mixed
	 */
	abstract public function invalidateList();

	/**
	 * Create filter
	 * @return mixed
	 */
	abstract protected function getFilter();

	/**
	 * Create filter form
	 * @param Form $form
	 * @return mixed
	 */
	abstract protected function createFilterForm(Form $form);

	#endregion

	#region "Implementation"

	public function __construct($parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		// create paginator
		$this->paginator = new \VisualPaginator($this, 'paginator');
	}

	/**
	 * Initialize paginator
	 * @return \VisualPaginator
	 */
	protected function createPaginator() {
		/** @var Paginator $pager */
		$pager = $this->paginator->getPaginator();
		$pager->itemsPerPage = $this->itemsPerPage;
		$pager->itemCount = $this->getTotalItemsCount();

		return $this->paginator;
	}

	/**
	 * Create filter form component
	 * @return Form
	 */
	protected function createComponentFilterForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$form->addText('itemsCount', 'items per page', 2)
			->addRule(\Nette\Forms\Form::INTEGER, 'Items count must be a number')
			->setDefaultValue(10)
			->getControlPrototype()->onBlur('$(this).parents("form:eq(0)").submit();');

		$form->addSubmit('filter', 'Update list');
		$form->onSuccess[] = callback($this, 'onFilterFormSuccess');

		$this->createFilterForm($form);

		return $form;
	}

	/**
	 * Save filter form
	 * @param Form $form
	 */
	public function onFilterFormSuccess(Form $form) {
		$values = $form->getValues();

		$this->filter = array();
		foreach ($values as $key => $value) {
			if ($key == 'itemsCount')
				continue;

			$this->filter[$key] = $value;
		}

		$this->itemsPerPage = ((int)$values['itemsCount'] > 0) ? (int) $values['itemsCount'] : 10;

		$this->invalidateList();
	}

	#endregion

	#region "Get & set"

	public function setScenarioStep(ScenarioStep $scenarioStep)
	{
		$this->scenarioStep = $scenarioStep;
	}

	public function getScenarioStep()
	{
		return $this->scenarioStep;
	}

	#endregion
}