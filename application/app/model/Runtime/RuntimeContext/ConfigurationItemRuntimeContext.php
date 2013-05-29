<?php
/**
 * ConfigurationItemRuntimeContext.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.4.13 13:03
 */

namespace ITILSimulator\Runtime\RuntimeContext;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationEventEvent;
use ITILSimulator\Runtime\RuntimeContext\RuntimeContext;
use ITILSimulator\Runtime\Training\ActiveConfigurationItem;
use ITILSimulator\Runtime\RuntimeContext\ActiveConfigurationItemFacade;
use Nette\Diagnostics\Debugger;

/**
 * Runtime class for custom behavior code for configuration items.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
class ConfigurationItemRuntimeContext extends RuntimeContext
{
	/** @var ActiveConfigurationItem */
	protected $activeConfigurationItem;

	public function __construct(ActiveConfigurationItem $activeConfigurationItem, EventManager $eventManager){
		$this->eventManager = $eventManager;
		$this->activeConfigurationItem = $activeConfigurationItem;
	}

	/**
	 * Execute custom behavior function
	 * @param string $function PHP code to be executed
	 * @param Event $event Event to be passed to the function
	 * @return mixed
	 */
	public function execute($function, $event)
	{
		$result = NULL;

		$this->registerErrorHandler();

		try {
			$callback = create_function('$_helpers,$_context,$eventManager,$event', $function);

			$result = $callback(
				new RuntimeContextHelper(),
				new ActiveConfigurationItemFacade($this->activeConfigurationItem, $this),
				$this->eventManager,
				$event
			);

		} catch (\Exception $e) {
			Debugger::log($e->getMessage());
		}

		$this->restoreErrorHandler();

		return $result;
	}

	/**
	 * Execute custom configuration item input behavior function
	 * @param string $function PHP code to be executed
	 * @param string $inputCode Code of CI input which was generated
	 * @return mixed
	 */
	public function executeInput($function, $inputCode)
	{
		$callback = create_function('$_helpers,$_context,$inputCode', $function);

		return $callback(
			new RuntimeContextHelper(),
			new ActiveConfigurationItemFacade($this->activeConfigurationItem, $this),
			$inputCode
		);
	}

	/**
	 * @param $code
	 * @param $description
	 */
	public function createEvent($code, $description) {
		$operationEvent = new OperationEvent();
		$operationEvent->setSource($this->activeConfigurationItem->getConfigurationItem()->getName());
		$operationEvent->setArchived(false);
		$operationEvent->setCode($code);
		$operationEvent->setDescription($description);
		$operationEvent->setDate(new \DateTime());

		$event = new OperationEventEvent();
		$event->setOperationEvent($operationEvent);

		$this->eventManager->dispatch(EventTypeEnum::SERVICE_EVENT_CREATED, $event);
	}
}