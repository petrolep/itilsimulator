<?php
/**
 * DesignerPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 17:22
 */

namespace TrainerModule;


use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\ScenarioDesignResult;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Runtime\Training\DesignResult;
use ITILSimulator\Services\DesignService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Presenters\TrainerPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;

/**
 * Service design presenter
 * @package TrainerModule
 */
class DesignerPresenter extends TrainerPresenter
{
	#region "Properties"

	/** @persistent */
	public $trainingStepId;

	/** @var TrainingService */
	protected $trainingService;

	/** @var DesignService */
	protected $designService;

	/** @var TrainingStep */
	protected $trainingStep;

	/** @var Scenario */
	protected $scenario;

	/** @var ConfigurationItem[] */
	protected $configurationItems = array();

	/** @var DesignResult */
	protected $designResult;

	/** @var array List of required inputs */
	protected $requiredInputs = array();

	/** @var array Cache of used configuration items */
	protected $usedConfigurationItems = array();

	/** @var array Log of found errors */
	protected $errors = array();

	#endregion

	#region "Lifecycle methods"

	public function inject(TrainingService $trainingService, DesignService $designService) {
		$this->trainingService = $trainingService;
		$this->designService = $designService;
	}

	public function startup() {
		parent::startup();

		$this->designResult = new DesignResult();

		$trainingStep = $this->sessionService->getTrainingStep($this->trainingStepId);

		if (!$trainingStep || $trainingStep->getUserId() != $this->getUserId()) {
			throw new BadRequestException('Training ' . $this->trainingStepId . ' can not be found for user ' . $this->getUserId() . '.');
		}

		if (!$trainingStep->getTraining()->isPublished()) {
			// training not published
			$this->forbidden();
		}

		if ($trainingStep->isFinished()) {
			// training finished, redirect to statistics
			$this->redirectFinishedTrainingStep($trainingStep);
		}

		if (!$trainingStep->isDesign()) {
			$this->redirect('training:default', array('trainingStepId' => $trainingStep->getId()));
		}

		$this->trainingStep = $trainingStep;
	}

	#endregion

	/**
	 * Design service
	 * @throws \RuntimeException
	 */
	public function actionDefault() {
		$this->scenario = $this->trainingStep->getScenario();
		$service = $this->scenario->getDesignService();

		if (!$service) {
			throw new \RuntimeException('No design service is assigned.');
		}

		// set all available Configuration Items from assigned services
		foreach ($this->scenario->getServices() as $availableService) {
			foreach ($availableService->getConfigurationItems() as $ci) {
				$this->configurationItems[$ci->getId()] = $ci;
			}
		}

		// get required inputs & outputs from assigned designing service
		$producingInputs = array();
		$producingOutputs = array();

		foreach ($service->getConfigurationItems() as $ci) {
			$this->configurationItems[$ci->getId()] = $ci;
			foreach ($ci->getOutputs() as $output) {
				$producingOutputs[$output->getId()] = true;
			}

			foreach ($ci->getInputs() as $input) {
				$producingInputs[$input->getId()] = true;
			}
		}

		// required inputs are all inputs which can be products by other CIs
		$this->requiredInputs = array_keys(array_intersect_key($producingInputs, $producingOutputs));
	}

	/**
	 * Design service
	 */
	public function renderDefault() {
		$position = 0;

		$html = array();
		foreach ($this->configurationItems as $ci) {
			$html[] = $this->renderCI($ci, $position++);
		}

		$this->template->configurationItems = $html;

		// sort CIs by name
		$templateCi = $this->configurationItems;
		usort($templateCi, function(ConfigurationItem $ci1, ConfigurationItem $ci2) { if ($ci1->getName() > $ci2->getName()) return 1; return $ci1->getName() == $ci2->getName() ? 0 : -1; });
		$this->template->configurationItemsRaw = $templateCi ;

		$this->template->connections = $this->designResult->getConnections();
		$this->template->showSolution = false;
		$this->template->scenario = $this->scenario;

		if ($this['solutionForm']->isSubmitted()) {
			// show solution
			$this->template->showSolution = true;
			$this->template->errors = $this->errors;
			$this->template->purchaseCosts = $this->designResult->getPurchaseCosts();
			$this->template->operationCosts = $this->designResult->getOperationCosts();
			$this->template->usedConfigurationItems = $this->usedConfigurationItems;
			$this->template->isDesignValid = !$this->errors;
		}
	}

	#region "Components"

	/**
	 * Create solution form component
	 * @return Form
	 */
	public function createComponentSolutionForm() {
		$form = new Form();
		$form->addHidden('relations');
		$form->addHidden('positions');
		$form->addHidden('final');

		$form->addSubmit('save', 'save');

		$form->onSuccess[] = $this->onSolutionFormSuccess;

		return $form;
	}

	/**
	 * Save solution form
	 * @param Form $form
	 */
	public function onSolutionFormSuccess(Form $form) {
		$values = $form->getValues();

		$data = json_decode($values['relations']);

		if (is_array($data->update) && is_array($data->create)) {
			// parse connections among configuration items
			$connections = array_merge($data->update, $data->create);
			foreach ($connections as $key => $connection) {
				$connection->id = $key + 1;
				$connection->source = $this->parseActivityHTMLId($connection->source);
				$connection->target = $this->parseActivityHTMLId($connection->target);
			}
			$this->designResult->setConnections($connections);
		}

		$training = $this->scenario->getTraining();

		// parse positions of individual configuration items
		$positions = json_decode($values['positions']);
		$convertedPositions = array();
		foreach($positions as $position) {
			$convertedPositions[$position->id] = (object)array('x' => $position->x, 'y' => $position->y);
		}
		$this->designResult->setPositions($convertedPositions);

		// check connections input <-> output
		$inputNames = array();
		foreach ($training->getInputsOutputs() as $io) {
			$inputNames[$io->getId()] = $io->getName();
		}

		$validation = $this->validateService();
		foreach ($validation['missingRequiredInputs'] as $inputId) {
			// missing required input
			$this->errors[] = sprintf($this->translator->translate('%s is required to be used to fulfill service purpose.'), isset($inputNames[$inputId]) ? ucfirst($inputNames[$inputId]) : '');
		}

		foreach ($validation['inputs'] as $missing) {
			// missing connection from input
			$this->errors[] = sprintf($this->translator->translate('%s is missing input %s'), $missing['ci']->getName(), $missing['input']->getName());
		}

		foreach ($validation['outputs'] as $missing) {
			// missing connection from output
			$this->errors[] = sprintf($this->translator->translate('%s is missing output connection for %s'), $missing['ci']->getName(), $missing['output']->getName());
		}

		if(!$this->errors && $values['final']) {
			// if the solution is final, save it
			$this->saveSolution($this->designResult, $this->trainingStep);
		}

		if ($this->isAjax()) {
			$this->invalidateControl('continuesCheck');
		}
	}

	#endregion

	#region "Helpers"

	/**
	 * Validate service -- check its inputs and outputs.
	 * @return array
	 */
	protected function validateService() {
		$missingInputs = array();
		$missingOutputs = array();

		$usedInputs = array();
		$purchaseCosts = 0;
		$operationCosts = 0;

		foreach ($this->configurationItems as $ci) {
			if (!$this->isCIUsed($ci))
				// CI is not used and is not required
				continue;

			// CI is used
			list($purchaseCosts, $operationCosts, $usedInputs, $missingInputs, $missingOutputs) = $this->parseConfigurationItem($ci, $purchaseCosts, $operationCosts, $usedInputs, $missingInputs, $missingOutputs);
		}

		$this->designResult->setPurchaseCosts($purchaseCosts);
		$this->designResult->setOperationCosts($operationCosts);

		return array(
			'inputs' => $missingInputs,
			'missingRequiredInputs' => array_diff($this->requiredInputs, array_keys($usedInputs)),
			'outputs' => $missingOutputs,
		);
	}

	/**
	 * Check whether given CI produces given output
	 * @param ConfigurationItem $ci
	 * @param int $outputId Input/Output ID
	 * @return bool
	 */
	protected function hasOutput(ConfigurationItem $ci, $outputId) {
		foreach ($this->configurationItems[$ci->getId()]->getOutputs() as $output) {
			if ($output->getId() == $outputId)
				return true;
		}

		return false;
	}

	/**
	 * Check whether given CI accepts given input
	 * @param ConfigurationItem $ci
	 * @param int $inputId Input/Output ID
	 * @return bool
	 */
	protected function hasInput(ConfigurationItem $ci, $inputId) {
		foreach ($this->configurationItems[$ci->getId()]->getInputs() as $input) {
			if ($input->getId() == $inputId)
				return true;
		}

		return false;
	}

	/**
	 * Check whether given CI is used in the design created by user
	 * @param ConfigurationItem $ci
	 * @return bool
	 */
	protected function isCIUsed(ConfigurationItem $ci) {
		foreach ($this->designResult->getConnections() as $connection) {
			if ($connection->source == $ci->getId() || $connection->target == $ci->getId())
				return true;
		}

		return false;
	}

	/**
	 * Render configuration item
	 * @param ConfigurationItem $ci
	 * @param $position
	 * @return string
	 */
	protected function renderCI(ConfigurationItem $ci, $position) {
		$positions = $this->designResult->getPositions();
		$x = isset($positions[$ci->getId()]) ? $positions[$ci->getId()]->x : ($position % 3 * 250);
		$y = isset($positions[$ci->getId()]) ? $positions[$ci->getId()]->y : (floor($position / 3) * 100 + 20);

		$el = Html::el('div', array(
			'class' => 'item',
			'data-id' => $ci->getId(),
			'id' => $this->generateActivityHTMLId($ci->getId()),
			'style' => 'left: ' . $x . 'px; top: ' . $y . 'px',
		));
		$el->setText($ci->getName());
		$el->create('span class="handle"')->addTitle($this->translator->translate('Click and drag to connect CI'));

		$tooltip = $el->create('div class="tooltip"');
		$ul = $tooltip->create('ul');

		$this->createTitleLi($ul->create('li'), $this->translator->translate('Purchase costs:'), TemplateHelpers::currency($ci->getDefaultSpecification()->getPurchaseCosts()));
		$this->createTitleLi($ul->create('li'), $this->translator->translate('Operational costs:'), TemplateHelpers::currency($ci->getDefaultSpecification()->getOperationalCosts()));

		if ($ci->getInputs()->count())
			$this->createTitleLi($ul->create('li'), $this->translator->translate('Required inputs:'), implode(', ', array_map(function($io) { return $io->getName();}, $ci->getInputs()->toArray())));

		if ($ci->getOutputs()->count())
			$this->createTitleLi($ul->create('li'), $this->translator->translate('Producing outputs:'), implode(', ', array_map(function($io) { return $io->getName();}, $ci->getOutputs()->toArray())));

		return $el->render();
	}

	/**
	 * Add to HTML $element title and its value.
	 * @param $element
	 * @param $title
	 * @param $value
	 */
	protected function createTitleLI(Html $element, $title, $value) {
		$element->create('span class="attr-title"', $title);
		$element->create('span class="attr-value"', $value);
	}

	/**
	 * Generate HTML activity ID.
	 * @param $id
	 * @return string
	 */
	public function generateActivityHTMLId($id) {
		return 'a' . $id;
	}

	/**
	 * Parse activity ID from its HTML representation.
	 * @param $id
	 * @return string
	 */
	public function parseActivityHTMLId($id) {
		return substr($id, 1);
	}

	/**
	 * Save solution produced by user.
	 * @param DesignResult $designResult
	 * @param TrainingStep $trainingStep
	 */
	protected function saveSolution(DesignResult $designResult, TrainingStep $trainingStep)
	{
		$result = new ScenarioDesignResult();
		$result->setMetadata(serialize($designResult));
		$result->setPurchaseCost($designResult->getPurchaseCosts());
		$result->setOperationCost($designResult->getOperationCosts());

		$this->designService->finishDesign($result, $trainingStep);
		$this->designService->commitChanges();

		$this->redirectFinishedTrainingStep($trainingStep);
	}

	/**
	 * Parse configuration item state from submitted solution.
	 * @param $ci
	 * @param $purchaseCosts
	 * @param $operationCosts
	 * @param $usedInputs
	 * @param $missingInputs
	 * @param $missingOutputs
	 * @return array
	 */
	protected function parseConfigurationItem(ConfigurationItem $ci, $purchaseCosts, $operationCosts, $usedInputs, $missingInputs, $missingOutputs)
	{
		$this->usedConfigurationItems[] = $ci;
		$purchaseCosts += $ci->getDefaultSpecification()->getPurchaseCosts();
		$operationCosts += $ci->getDefaultSpecification()->getOperationalCosts();

		// check inputs
		foreach ($ci->getInputs() as $input) {
			$receivesInput = false;
			foreach ($this->designResult->getConnections() as $connection) {
				if ($connection->target == $ci->getId() && isset($this->configurationItems[$connection->source])
					&& $this->hasOutput($this->configurationItems[$connection->source], $input->getId())
				) {
					$receivesInput = true;
					$usedInputs[$input->getId()] = true;

					break;
				}
			}

			if (!$receivesInput) {
				$missingInputs[] = array('ci' => $ci, 'input' => $input);
			}
		}

		// check outputs
		foreach ($ci->getOutputs() as $output) {
			if (!in_array($output->getId(), $this->requiredInputs)) {
				continue;
			}

			$sendsOutput = false;

			foreach ($this->designResult->getConnections() as $connection) {
				if ($connection->source == $ci->getId() && isset($this->configurationItems[$connection->target])
					&& $this->hasInput($this->configurationItems[$connection->target], $output->getId())
				) {
					$sendsOutput = true;

					break;
				}
			}

			if (!$sendsOutput) {
				$missingOutputs[] = array('ci' => $ci, 'output' => $output);
			}
		}

		return array($purchaseCosts, $operationCosts, $usedInputs, $missingInputs, $missingOutputs);
	}

	#endregion
}