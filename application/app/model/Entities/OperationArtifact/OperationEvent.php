<?php
/**
 * OperationIncident.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:16
 */

namespace ITILSimulator\Entities\OperationArtifact;

/**
 * Operation event class (Doctrine entity). Inherits OperationArtifact.
 * @Entity(repositoryClass="ITILSimulator\Repositories\OperationArtifact\OperationEventRepository")
 * @Table(name="operation_events")
 **/
class OperationEvent extends OperationArtifact
{
	const STATUS_NEW = 1;
	const STATUS_ARCHIVED = 2;

	#region "Properties"

	/**
	 * @Column(type="string", length=20, nullable=true)
	 * @var string
	 */
	protected $source;

	/**
	 * @Column(type="string", length=50, nullable=true)
	 * @var string
	 */
	protected $code;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	#endregion

	/**
	 * Mark event as archived
	 */
	public function archive() {
		$this->setArchived(true);
	}

	#region "Get & set"

	/**
	 * Set source configuration item code
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	/**
	 * Get source configuration item code
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Mark event as archived or new
	 * @param boolean $isArchived TRUE to archived, FALSE to new
	 */
	public function setArchived($isArchived = true)
	{
		$this->setStatus($isArchived ? self::STATUS_ARCHIVED : self::STATUS_NEW);
	}

	/**
	 * Whether the event is archived
	 * @return boolean
	 */
	public function isArchived()
	{
		return $this->getIsArchived();
	}

	/**
	 * Whether the event is archived
	 * @return bool
	 */
	public function getIsArchived()
	{
		return $this->getStatus() == self::STATUS_ARCHIVED;
	}

	/**
	 * Set event code
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * Get event code
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
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

	#endregion

}