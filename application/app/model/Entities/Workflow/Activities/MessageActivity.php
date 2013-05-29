<?php
/**
 * MessageActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 17:41
 */

namespace ITILSimulator\Entities\Workflow\Activities;

use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\MessageEvent;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Message activity (Doctrine entity). Displays info/warning/error/success messages to user.
 * Inherits WorkflowActivity.
 * @Entity
 */
class MessageActivity extends WorkflowActivity
{
	#region "Properties"

	/**
	 * @Column(type="string", length=30, nullable=true)
	 * @var string
	 */
	protected $type;

	/**
	 * @Column(type="string", length=80, nullable=true)
	 * @var string
	 */
	protected $title;

	#endregion

	#region "Get & set"

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	#endregion

	#region "Events"

	/**
	 * Executed when the workflow activity starts. Fires ACTIVITY_MESSAGE_CREATED event.
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null|void
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		parent::onStart($context);

		$context->getEventManager()->dispatch(EventTypeEnum::ACTIVITY_MESSAGE_CREATED, $this->getMessageEvent());
	}

	/**
	 * Executed when running activity is restored (during HTTP request).
	 * Attaches listener for ACTIVITY_MESSAGE_ACCEPTED event to be able to react on message acceptation from user.
	 * Also fires ACTIVITY_MESSAGE_PENDING to signalize a pending message.
	 * @param WorkflowActivityRuntimeContext $context
	 */
	public function onRestore(WorkflowActivityRuntimeContext $context) {
		parent::onRestore($context);

		$that = $this;
		$context->getEventManager()->addListener(EventTypeEnum::ACTIVITY_MESSAGE_ACCEPTED, function ($event) use ($context, $that) {
			if ($event instanceof MessageEvent && $event->getName() == EventTypeEnum::ACTIVITY_MESSAGE_ACCEPTED
				&& $event->getWorkflowActivityId() == $that->id) {
				// message accepted, finish the activity
				$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND,  $event);
			}
		});

		$context->getEventManager()->dispatch(EventTypeEnum::ACTIVITY_MESSAGE_PENDING, $that->getMessageEvent());
	}

	#endregion

	public function __toString() {
		return sprintf('[MessageActivity] #%d: %s', $this->id, $this->description);
	}

	/**
	 * @return MessageEvent
	 */
	protected  function getMessageEvent()
	{
		$event = new MessageEvent();
		$event->setTitle($this->getTitle());
		$event->setDescription($this->getDescription());
		$event->setMessageType($this->getType());
		$event->setWorkflowActivityId($this->id);

		return $event;
	}
}