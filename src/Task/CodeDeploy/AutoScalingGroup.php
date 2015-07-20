<?php
/**
 * AutoScalingGroup.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        AutoScalingGroup.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CodeDeploy;

/**
 * AutoScalingGroup
 *
 * @package     Aws
 * @subpackage  CodeDeploy
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class AutoScalingGroup
{

    /**
     * @var string
     */
    protected $name;

    /**
     * Return the string representation of the param
     * @return string
     */
    public function __toString() {
        return $this->getName();
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
     * @return string
     */
    public function getName() {
        return (string)$this->name;
    }
}
