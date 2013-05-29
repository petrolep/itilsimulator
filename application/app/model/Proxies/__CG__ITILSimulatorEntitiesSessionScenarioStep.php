<?php

namespace Proxies\__CG__\ITILSimulator\Entities\Session;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class ScenarioStep extends \ITILSimulator\Entities\Session\ScenarioStep implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    /** @private */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    
    public function undo()
    {
        $this->__load();
        return parent::undo();
    }

    public function getTrainingStepId()
    {
        $this->__load();
        return parent::getTrainingStepId();
    }

    public function getConfigurationItemsSpecifications()
    {
        $this->__load();
        return parent::getConfigurationItemsSpecifications();
    }

    public function getDate()
    {
        $this->__load();
        return parent::getDate();
    }

    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();
        return parent::getId();
    }

    public function isUndid()
    {
        $this->__load();
        return parent::isUndid();
    }

    public function getServicesSpecifications()
    {
        $this->__load();
        return parent::getServicesSpecifications();
    }

    public function getTrainingStep()
    {
        $this->__load();
        return parent::getTrainingStep();
    }

    public function setServicesSpecifications($serviceSpecifications)
    {
        $this->__load();
        return parent::setServicesSpecifications($serviceSpecifications);
    }

    public function setConfigurationItemsSpecifications($configurationItemsSpecifications)
    {
        $this->__load();
        return parent::setConfigurationItemsSpecifications($configurationItemsSpecifications);
    }

    public function setWorkflowActivitiesSpecifications($workflowActivitiesSpecifications)
    {
        $this->__load();
        return parent::setWorkflowActivitiesSpecifications($workflowActivitiesSpecifications);
    }

    public function getWorkflowActivitiesSpecifications()
    {
        $this->__load();
        return parent::getWorkflowActivitiesSpecifications();
    }

    public function setEvaluationPoints($evaluationPoints)
    {
        $this->__load();
        return parent::setEvaluationPoints($evaluationPoints);
    }

    public function getEvaluationPoints()
    {
        $this->__load();
        return parent::getEvaluationPoints();
    }

    public function setBudget($budget)
    {
        $this->__load();
        return parent::setBudget($budget);
    }

    public function getBudget()
    {
        $this->__load();
        return parent::getBudget();
    }

    public function setInternalTime($internalTime)
    {
        $this->__load();
        return parent::setInternalTime($internalTime);
    }

    public function getInternalTime()
    {
        $this->__load();
        return parent::getInternalTime();
    }

    public function setUndoDate($undoDate)
    {
        $this->__load();
        return parent::setUndoDate($undoDate);
    }

    public function getUndoDate()
    {
        $this->__load();
        return parent::getUndoDate();
    }

    public function setServicesSettlementTime($servicesSettlementTime)
    {
        $this->__load();
        return parent::setServicesSettlementTime($servicesSettlementTime);
    }

    public function getServicesSettlementTime()
    {
        $this->__load();
        return parent::getServicesSettlementTime();
    }

    public function setLastActivityDate($lastActivityDate)
    {
        $this->__load();
        return parent::setLastActivityDate($lastActivityDate);
    }

    public function getLastActivityDate()
    {
        $this->__load();
        return parent::getLastActivityDate();
    }

    public function __call($name, $args)
    {
        $this->__load();
        return parent::__call($name, $args);
    }

    public function &__get($name)
    {
        $this->__load();
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $this->__load();
        return parent::__set($name, $value);
    }

    public function __isset($name)
    {
        $this->__load();
        return parent::__isset($name);
    }

    public function __unset($name)
    {
        $this->__load();
        return parent::__unset($name);
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'date', 'undoDate', 'lastActivityDate', 'isUndid', 'evaluationPoints', 'budget', 'internalTime', 'servicesSettlementTime', 'trainingStep', 'servicesSpecifications', 'configurationItemsSpecifications', 'workflowActivitiesSpecifications');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields as $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}