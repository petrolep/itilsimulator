<?php

namespace Proxies\__CG__\ITILSimulator\Entities\Simulator;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class User extends \ITILSimulator\Entities\Simulator\User implements \Doctrine\ORM\Proxy\Proxy
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

    
    public function validatePassword($password, $hashType)
    {
        $this->__load();
        return parent::validatePassword($password, $hashType);
    }

    public function getRolesList()
    {
        $this->__load();
        return parent::getRolesList();
    }

    public function clearRoles()
    {
        $this->__load();
        return parent::clearRoles();
    }

    public function addRole(\ITILSimulator\Entities\Simulator\Role $role)
    {
        $this->__load();
        return parent::addRole($role);
    }

    public function generateSalt()
    {
        $this->__load();
        return parent::generateSalt();
    }

    public function setPassword($password, $hashType)
    {
        $this->__load();
        return parent::setPassword($password, $hashType);
    }

    public function getPassword()
    {
        $this->__load();
        return parent::getPassword();
    }

    public function setEmail($email)
    {
        $this->__load();
        return parent::setEmail($email);
    }

    public function getEmail()
    {
        $this->__load();
        return parent::getEmail();
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

    public function setDateLastLogin($dateLastLogin)
    {
        $this->__load();
        return parent::setDateLastLogin($dateLastLogin);
    }

    public function getDateLastLogin()
    {
        $this->__load();
        return parent::getDateLastLogin();
    }

    public function setDateRegistration($dateRegistration)
    {
        $this->__load();
        return parent::setDateRegistration($dateRegistration);
    }

    public function getDateRegistration()
    {
        $this->__load();
        return parent::getDateRegistration();
    }

    public function setRoles($roles)
    {
        $this->__load();
        return parent::setRoles($roles);
    }

    public function getRoles()
    {
        $this->__load();
        return parent::getRoles();
    }

    public function setSessions($sessions)
    {
        $this->__load();
        return parent::setSessions($sessions);
    }

    public function getSessions()
    {
        $this->__load();
        return parent::getSessions();
    }

    public function setIsAnonymous($isAnonymous)
    {
        $this->__load();
        return parent::setIsAnonymous($isAnonymous);
    }

    public function getIsAnonymous()
    {
        $this->__load();
        return parent::getIsAnonymous();
    }

    public function isAnonymous()
    {
        $this->__load();
        return parent::isAnonymous();
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
        return array('__isInitialized__', 'id', 'name', 'email', 'password', 'passwordSalt', 'dateRegistration', 'dateLastLogin', 'isAnonymous', 'roles', 'sessions');
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