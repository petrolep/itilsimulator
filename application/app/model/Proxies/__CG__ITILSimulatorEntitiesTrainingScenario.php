<?php

namespace Proxies\__CG__\ITILSimulator\Entities\Training;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Scenario extends \ITILSimulator\Entities\Training\Scenario implements \Doctrine\ORM\Proxy\Proxy
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

    
    public function assignService(\ITILSimulator\Entities\Training\Service $service)
    {
        $this->__load();
        return parent::assignService($service);
    }

    public function unassignService(\ITILSimulator\Entities\Training\Service $service)
    {
        $this->__load();
        return parent::unassignService($service);
    }

    public function setDescription($description)
    {
        $this->__load();
        return parent::setDescription($description);
    }

    public function getDescription()
    {
        $this->__load();
        return parent::getDescription();
    }

    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();
        return parent::getId();
    }

    public function setName($name)
    {
        $this->__load();
        return parent::setName($name);
    }

    public function getName()
    {
        $this->__load();
        return parent::getName();
    }

    public function setInitialBudget($initialBudget)
    {
        $this->__load();
        return parent::setInitialBudget($initialBudget);
    }

    public function getInitialBudget()
    {
        $this->__load();
        return parent::getInitialBudget();
    }

    public function setTraining($training)
    {
        $this->__load();
        return parent::setTraining($training);
    }

    public function getTraining()
    {
        $this->__load();
        return parent::getTraining();
    }

    public function getServices()
    {
        $this->__load();
        return parent::getServices();
    }

    public function getWorkflows()
    {
        $this->__load();
        return parent::getWorkflows();
    }

    public function addWorkflow(\ITILSimulator\Entities\Workflow\Workflow $workflow)
    {
        $this->__load();
        return parent::addWorkflow($workflow);
    }

    public function removeWorkflow(\ITILSimulator\Entities\Workflow\Workflow $workflow)
    {
        $this->__load();
        return parent::removeWorkflow($workflow);
    }

    public function setType($type)
    {
        $this->__load();
        return parent::setType($type);
    }

    public function getType()
    {
        $this->__load();
        return parent::getType();
    }

    public function isDesign()
    {
        $this->__load();
        return parent::isDesign();
    }

    public function getIsDesign()
    {
        $this->__load();
        return parent::getIsDesign();
    }

    public function setDesignService($designService)
    {
        $this->__load();
        return parent::setDesignService($designService);
    }

    public function getDesignService()
    {
        $this->__load();
        return parent::getDesignService();
    }

    public function setDetailDescription($longDescription)
    {
        $this->__load();
        return parent::setDetailDescription($longDescription);
    }

    public function getDetailDescription()
    {
        $this->__load();
        return parent::getDetailDescription();
    }

    public function getTrainingId()
    {
        $this->__load();
        return parent::getTrainingId();
    }

    public function getCreatorUserId()
    {
        $this->__load();
        return parent::getCreatorUserId();
    }

    public function isAvailableForUser($user)
    {
        $this->__load();
        return parent::isAvailableForUser($user);
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
        return array('__isInitialized__', 'id', 'name', 'description', 'detailDescription', 'initialBudget', 'type', 'training', 'services', 'workflows', 'trainingSteps', 'designService');
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