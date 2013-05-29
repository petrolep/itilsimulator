<?php
/**
 * UImessage.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 19:35
 */

namespace ITILSimulator\Runtime\UI;


use Nette\Object;

/**
 * UI message (message displayed to user during service operation)
 * @package ITILSimulator\Runtime\UI
 */
class UIMessage extends Object
{
	/** @var string Message title */
	protected $title;

	/** @var string Message text */
	protected $text;

	/** @var string Custom param of "Close" button -- can cause callback to server to detect message acceptance */
	protected $param;

	/** @var string Type of message (see MessageTypeEnum) */
	protected $type;

	/** @var string Signature (MD5 hash) of message to detect duplicate messages */
	protected $guid;

	/** @var bool */
	protected $isNew = true;

	/**
	 * @param string $title Message title
	 * @param string $text Message text
	 * @param string $type Type of message (see MessageTypeEnum)
	 * @param string|null $param Custom param of "Close" button -- can cause callback to server to detect message acceptance
	 */
	public function __construct($title, $text, $type = MessageTypeEnum::INFO, $param = null) {
		$this->title = $title;
		$this->text = $text;
		$this->type = $type;
		$this->param = $param;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
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

	/**
	 * @param string $param
	 */
	public function setParam($param)
	{
		$this->param = $param;
	}

	/**
	 * @return string
	 */
	public function getParam()
	{
		return $this->param;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $guid
	 */
	public function setGuid($guid)
	{
		$this->guid = $guid;
	}

	/**
	 * @return string
	 */
	public function getGuid()
	{
		return $this->guid;
	}

	/**
	 * @param boolean $isNew
	 */
	public function setIsNew($isNew)
	{
		$this->isNew = $isNew;
	}

	/**
	 * @return boolean
	 */
	public function getIsNew()
	{
		return $this->isNew;
	}

	public function isNew()
	{
		return $this->getIsNew();
	}

}