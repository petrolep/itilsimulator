<?php
/**
 * Event.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 20:51
 */

namespace ITILSimulator\Runtime\Events;


use Nette\Object;

/**
 * General EventManager event
 * @package ITILSimulator\Runtime\Events
 */
class Event extends Object
{
	#region "Properties"

	/** @var string */
	protected $name;

	/** @var bool */
	protected $stopPropagation = false;

	/** @var string */
	protected $description;

	/** @var string */
	protected $code;

	/** @var string */
	protected $source;

	#endregion

	#region "Get & set"

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->setName($type);
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->name;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	public function isPropagationStopped()
	{
		return $this->stopPropagation;
	}

	public function stopPropagation()
	{
		$this->stopPropagation = true;
	}

	#endregion
}