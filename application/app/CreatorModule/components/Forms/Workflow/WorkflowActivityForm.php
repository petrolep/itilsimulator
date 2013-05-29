<?php
/**
 * WorkflowActivityFormControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 13:34
 */

namespace ITILSimulator\Creator\Components\Forms\Workflow;


use ITILSimulator\Base\BaseForm;
use ITILSimulator\Base\JavaScriptTranslator\InvalidPHPException;
use ITILSimulator\Base\JavaScriptTranslator\JavaScriptTranslator;
use ITILSimulator\Base\JavaScriptTranslator\PHPValidator;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Workflow\Workflow;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\Application\UI\Form;

/**
 * Workflow activity form skeleton.
 * @package ITILSimulator\Creator\Components\Forms\Workflow
 */
abstract class WorkflowActivityForm extends BaseForm
{
	#region "Properties"

	/** @var Workflow */
	protected $workflow;

	/** @var WorkflowActivity */
	protected $workflowActivity;

	/** @var OperationCategory[] */
	protected $availableCategories = array();

	protected $defaultValues = array();

	protected $noCategoryText = 'no category';

	/** @var array Cache of compiled javascript codes */
	protected $compiledEvents = array();

	#endregion

	#region "Abstract methods"

	/**
	 * @return WorkflowActivity
	 */
	abstract public function getWorkflowActivity();

	abstract public function setDefaultValues(WorkflowActivity $workflowActivity);

	abstract public function setupControl();

	#endregion

	#region "Lifecycle methods"

	public function __construct($parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->setupControl();
	}

	public function attached($presenter) {
		parent::attached($presenter);

		if($default = $this->defaultValues) {
			// convert category object to ID
			if(isset($default['category']) && $default['category']) {
				foreach ($this->availableCategories as $category) {
					if ($category->getId() == $default['category']->getId()) {
						$default['category'] = $category->getId();

						break;
					}
				}
			}

			$this->setDefaults($default);
		}
	}

	#endregion

	/**
	 * Return available operation categories
	 * @return array
	 */
	protected function getCategoriesOptions() {
		$availableCategories = array();
		foreach ($this->availableCategories as $category) {
			$availableCategories[$category->getId()] = $category->getName();
		}

		return $availableCategories;
	}

	/**
	 * Compile javascript code to PHP
	 * @param $name
	 * @param $code
	 * @return array|null|string
	 */
	protected function compileJavaScript($name, $code) {
		$js = new JavaScriptTranslator();

		try {
			$translation = $js->translate($code);

			$validator = new PHPValidator($this->getHttpRequest()->getUrl()->baseUrl);
			if (!$validator->validate($translation)) {
				throw new InvalidPHPException;
			}

			return $translation;

		} catch(InvalidPHPException $e) {
			$this->addError($name . ': invalid code');

		} catch(\Exception $error) {
			$this->addError($name . ': ' . $error->getMessage());
		}
	}

	#region "Get & set"

	/**
	 * @param \ITILSimulator\Entities\Workflow\Workflow $workflow
	 */
	public function setWorkflow($workflow)
	{
		$this->workflow = $workflow;
	}

	/**
	 * @return \ITILSimulator\Entities\Workflow\Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	/**
	 * @param WorkflowActivity $workflowActivity
	 */
	public function setWorkflowActivity(WorkflowActivity $workflowActivity)
	{
		$this->workflowActivity = $workflowActivity;
	}

	public function setAvailableCategories($availableCategories)
	{
		$this->availableCategories = $availableCategories;
	}

	public function getAvailableCategories()
	{
		return $this->availableCategories;
	}

	#endregion
}