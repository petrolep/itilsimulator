<?php

namespace Proxies\__CG__\ITILSimulator\Entities\Training;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Training extends \ITILSimulator\Entities\Training\Training implements \Doctrine\ORM\Proxy\Proxy
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

    
    public function isAvailableForUser($user)
    {
        $this->__load();
        return parent::isAvailableForUser($user);
    }

    public function getUserId()
    {
        $this->__load();
        return parent::getUserId();
    }

    public function isCreatedByAnonymousUser()
    {
        $this->__load();
        return parent::isCreatedByAnonymousUser();
    }

    public function setIsPublic($isPublic)
    {
        $this->__load();
        return parent::setIsPublic($isPublic);
    }

    public function getIsPublic()
    {
        $this->__load();
        return parent::getIsPublic();
    }

    public function isPublic()
    {
        $this->__load();
        return parent::isPublic();
    }

    public function setIsPublished($isPublished)
    {
        $this->__load();
        return parent::setIsPublished($isPublished);
    }

    public function getIsPublished()
    {
        $this->__load();
        return parent::getIsPublished();
    }

    public function isPublished()
    {
        $this->__load();
        return parent::isPublished();
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

    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();
        return parent::getId();
    }

    public function getServices()
    {
        $this->__load();
        return parent::getServices();
    }

    public function addService(\ITILSimulator\Entities\Training\Service $service)
    {
        $this->__load();
        return parent::addService($service);
    }

    public function removeService(\ITILSimulator\Entities\Training\Service $service)
    {
        $this->__load();
        return parent::removeService($service);
    }

    public function setUser($user)
    {
        $this->__load();
        return parent::setUser($user);
    }

    public function getUser()
    {
        $this->__load();
        return parent::getUser();
    }

    public function getScenarios()
    {
        $this->__load();
        return parent::getScenarios();
    }

    public function addScenario(\ITILSimulator\Entities\Training\Scenario $scenario)
    {
        $this->__load();
        return parent::addScenario($scenario);
    }

    public function removeScenario(\ITILSimulator\Entities\Training\Scenario $scenario)
    {
        $this->__load();
        return parent::removeScenario($scenario);
    }

    public function getScenario($id)
    {
        $this->__load();
        return parent::getScenario($id);
    }

    public function setShortDescription($shortDescription)
    {
        $this->__load();
        return parent::setShortDescription($shortDescription);
    }

    public function getShortDescription()
    {
        $this->__load();
        return parent::getShortDescription();
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

    public function getOperationCategories()
    {
        $this->__load();
        return parent::getOperationCategories();
    }

    public function addOperationCategory(\ITILSimulator\Entities\OperationArtifact\OperationCategory $category)
    {
        $this->__load();
        return parent::addOperationCategory($category);
    }

    public function removeOperationCategory(\ITILSimulator\Entities\OperationArtifact\OperationCategory $category)
    {
        $this->__load();
        return parent::removeOperationCategory($category);
    }

    public function getInputsOutputs()
    {
        $this->__load();
        return parent::getInputsOutputs();
    }

    public function addInputOutput(\ITILSimulator\Entities\Training\InputOutput $io)
    {
        $this->__load();
        return parent::addInputOutput($io);
    }

    public function removeInputOutput(\ITILSimulator\Entities\Training\InputOutput $io)
    {
        $this->__load();
        return parent::removeInputOutput($io);
    }

    public function getKnownIssues()
    {
        $this->__load();
        return parent::getKnownIssues();
    }

    public function addKnownIssue(\ITILSimulator\Entities\Training\KnownIssue $issue)
    {
        $this->__load();
        return parent::addKnownIssue($issue);
    }

    public function removeKnownIssue(\ITILSimulator\Entities\Training\KnownIssue $issue)
    {
        $this->__load();
        return parent::removeKnownIssue($issue);
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
        return array('__isInitialized__', 'id', 'name', 'shortDescription', 'description', 'isPublished', 'isPublic', 'services', 'scenarios', 'user', 'operationCategories', 'inputsOutputs', 'knownIssues');
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