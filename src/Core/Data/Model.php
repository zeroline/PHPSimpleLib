<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

class Model implements \JsonSerializable
{
    /**
     *
     * @var array
     */
    protected $data = array();
/**
     *
     * @var array
     */
    protected $dirtyFields = array();
/**
     *
     * @var array
     */
    protected $serializableFields = array();
/**
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        if (!is_array($data) && is_object($data)) {
            $data = (array)$data;
        }
        $this->data = $data;
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!isset($this->data[$name]) || $this->data[$name] != $value) {
            $this->dirtyFields[$name] = $value;
        }
        $this->data[$name] = $value;
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     *
     * @return array
     */
    public function getDirtyFields(): array
    {
        return $this->dirtyFields;
    }

    /**
     *
     * @return array
     */
    public function getDirtyFieldNames(): array
    {
        return array_keys($this->dirtyFields);
    }

    /**
     *
     * @return bool
     */
    public function isDirty(): bool
    {
        return (bool)(count($this->dirtyFields) > 0);
    }

    /**
     *
     */
    public function clearDirtyFields()
    {
        $this->dirtyFields = array();
    }

    /**
     *
     * @return array
     */
    public function getExistingFieldNames(): array
    {
        return array_keys($this->data);
    }

    /**
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasExistingField(string $fieldName): bool
    {
        return in_array($fieldName, $this->getExistingFieldNames());
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if (count($this->serializableFields) > 0) {
            $arr = array();
            foreach ($this->serializableFields as $field) {
                $arr[$field] = $this->{$field};
            }
            return $arr;
        } else {
            return $this->data;
        }
    }
}
