<?php
/**
 * Created by PhpStorm.
 * User: antoine
 * Date: 01/07/2014
 * Time: 17:22
 */

namespace Aws\Task\CloudFormation;

class StackParam
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $usePreviousValue = false;

    /**
     * Return the array representation of the param
     * @return array
     */
    public function toArray() {
        return
        [
            'ParameterKey'      => $this->getName(),
            'ParameterValue'    => $this->getValue(),
            'UsePreviousValue'  => $this->getUsePreviousValue()
        ];
    }

    /**
     * @param mixed $usePreviousValue
     *
     * @return $this
     */
    public function setUsePreviousValue($usePreviousValue) {
        $this->usePreviousValue = $usePreviousValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsePreviousValue() {
        return $this->usePreviousValue;
    }


    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }




}