<?php
/**
 * StackParam.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        StackParam.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CloudFormation;

/**
 * StackParam
 *
 * @package     Aws
 * @subpackage  CloudFormation
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
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
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
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