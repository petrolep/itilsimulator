<?php
/**
 * WorkflowActivityRuntimeContext.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 19:50
 */

namespace ITILSimulator\Runtime\RuntimeContext;

use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\RuntimeContext\RuntimeContext;
use ITILSimulator\Runtime\Workflow\ActiveWorkflowActivity;
use Nette\Diagnostics\Debugger;

/**
 * Runtime class for custom behavior code for workflow activities.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
class WorkflowActivityRuntimeContext extends RuntimeContext
{
	/** Finish command in PHP */
	const FINISH_COMMAND = '$_context->finish();';

	/** @var ActiveWorkflowActivity */
	protected $activity;

	public function __construct(ActiveWorkflowActivity $activity, EventManager $eventManager) {
		$this->activity = $activity;
		$this->eventManager = $eventManager;
	}

	/**
	 * Execute custom code
	 * @param string $function
	 * @param Event $event
	 * @return mixed|null
	 */
	public function execute($function, $event) {
		$result = NULL;
		$this->registerErrorHandler();

		try {
			$callback = create_function('$_helpers,$_context,$event', $function);
			if ($callback) {
				if (!$event)
					$event = new Event();

				$result = $callback(
					new RuntimeContextHelper(),
					new ActiveWorkflowActivityFacade($this->activity, $this),
					$event
				);
			}

		} catch(\Exception $e) {
			Debugger::log($e->getMessage());
		}

		$this->restoreErrorHandler();

		return $result;
	}

	/**
	 * Dispatch event to event manager.
	 * @param $type
	 * @param $event
	 */
	public function dispatchEvent($type, $event) {
		$this->eventManager->dispatch($type, $event);
	}
}