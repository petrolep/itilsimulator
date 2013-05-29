<?php
/**
 * WorkflowActivityStateEnum.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 16:40
 */

namespace ITILSimulator\Runtime\Workflow;

/**
 * Enumeration of available workflow activity states
 * @package ITILSimulator\Runtime\Workflow
 */
class WorkflowActivityStateEnum
{
	const WAITING = 1;
	const RUNNING = 2;
	const FINISHED = 3;
	const CANCELLED = 4;
}