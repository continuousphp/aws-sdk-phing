<?php
/**
 * StackOutput.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        StackOutput.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CloudFormation;

/**
 * StackOutput
 *
 * @package     Aws
 * @subpackage  CloudFormation
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class StackOutput
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $property;

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
     * @param mixed $property
     *
     * @return $this
     */
    public function setProperty($property) {
        $this->property = $property;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperty() {
        return $this->property;
    }




}