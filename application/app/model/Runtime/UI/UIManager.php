<?php
/**
 * UIManager.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 19:30
 */

namespace ITILSimulator\Runtime\UI;


use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Runtime\Events\EvaluationEvent;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\MessageEvent;
use Nette\Localization\ITranslator;

/**
 * UI manager to display UI messages
 * @package ITILSimulator\Runtime\UI
 */
class UIManager
{
	/** @var UIMessage[] */
	protected $messages = array();

	/** @var \ITILSimulator\Runtime\Events\EventManager */
	protected $eventManager;

	/** @var ITranslator */
	protected $translator;

	public function __construct(EventManager $eventManager, ITranslator $translator) {
		$this->eventManager = $eventManager;
		$this->translator = $translator;
		$this->setupEventManager();
	}

	protected function setupEventManager() {
		$listeners = array(
			EventTypeEnum::ACTIVITY_MESSAGE_CREATED => 'onMessageCreated',
			EventTypeEnum::ACTIVITY_MESSAGE_PENDING => 'onMessagePending',
			EventTypeEnum::ACTIVITY_EVALUATION_CREATED => 'onEvaluationCreated',
		);

		foreach($listeners as $event => $listener) {
			$this->eventManager->addListener($event, array($this, $listener));
		}
	}

	/**
	 * New message created by Message workflow activity
	 * @param Event $event
	 */
	public function onMessageCreated(Event $event) {
		$msg = $this->createMessage($event);

		$this->registerMessage($msg);
	}

	/**
	 * Display pending messages (created by Message workflow activities)
	 * @param Event $event
	 */
	public function onMessagePending(Event $event) {
		$msg = $this->createMessage($event);
		$msg->setIsNew(false);

		$this->registerMessage($msg);
	}

	/**
	 * Display message about evaluation change
	 * @param Event $event
	 */
	public function onEvaluationCreated(Event $event) {
		if ($event instanceof EvaluationEvent) {
			$text = '';
			if ($event->getPoints())
				$text .= $event->getPoints() . ' ' . $this->translator->translate('points', $event->getPoints()) . "\n";

			if ($event->getMoney())
				$text .= TemplateHelpers::currency($event->getMoney());

			$msg = new UIMessage($text, $this->translator->translate('New evaluation'), MessageTypeEnum::EVALUATION);
			$this->registerMessage($msg);
		}
	}

	/**
	 * Add new UI message
	 * @param UIMessage $message
	 */
	public function registerMessage(UIMessage $message) {
		$this->messages[] = $message;
	}

	/**
	 * Accept UI message created by Message workflow activity
	 * @param $workflowActivityId
	 */
	public function acceptMessage($workflowActivityId) {
		$event = new MessageEvent();
		$event->setWorkflowActivityId($workflowActivityId);

		$this->eventManager->dispatch(EventTypeEnum::ACTIVITY_MESSAGE_ACCEPTED, $event);
	}

	/**
	 * Get available UI messages
	 * @return UIMessage[]
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @param Event $event
	 * @return UIMessage
	 */
	public function createMessage(Event $event)
	{
		if ($event instanceof MessageEvent) {
			// message workflow activity requires confirmation
			$msg = new UIMessage($event->getTitle(), $event->getDescription(), $event->getMessageType(), $event->getWorkflowActivityId());

			// generate unique guid for the message
			// allows detecting duplicating messages
			$msg->setGuid(md5($event->getDescription() . $event->getTitle() . $event->getMessageType() . $event->getWorkflowActivityId()));

		} else {
			$msg = new UIMessage($event->getName(), $event->getDescription());

			// generate unique guid for the message
			// allows detecting duplicating messages
			$msg->setGuid(md5($event->getDescription() . $event->getType()));
		}

		return $msg;
	}
}