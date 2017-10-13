<?php
/**
 * AutoScalingGroupDescribeTask.php
 *
 * @date        13/10/2017
 * @author      Pierre Tomasina <pierre.tomasina@continuousphp.com>
 * @copyright   Copyright (c) 2017 continuousphp (http://continuousphp.com)
 * @file        AutoScalingGroupDescribe.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Ec2;
use Aws\Task\AbstractTask;
use Aws\AutoScaling\AutoScalingClient;

/**
 * AutoScalingGroupDescribeTask
 *
 * @package     Aws
 * @subpackage  Ec2
 * @author      Pierre Tomasina <pierre.tomasina@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class AutoScalingGroupDescribeTask extends AbstractTask
{
    /**
     * AutoScalingGroup name
     * @var string
     */
    protected $name;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Property name to set with boolean if AutoScalingGroup exist.
     *
     * @var boolean
     */
    protected $asgExistProperty;

    /**
     * @var AutoScalingClient
     */
    protected $service;

    /**
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The name of property to set to output value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }

    /**
     * The name of property to set if AutoScalingGroup exist.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setAsgExistProperty($prop)
    {
        $this->asgExistProperty = $prop;
    }

    /**
     * @return AutoScalingClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->createAutoScaling();
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main()
    {
        $asg = $this->getService();

        $awsResult = $asg->describeAutoScalingGroups([
            'AutoScalingGroupNames' => [$this->getName()],
        ]);

        if (empty($awsResult['AutoScalingGroups'])) {
            if ($this->asgExistProperty) {
                $this->project->setProperty(
                    $this->asgExistProperty,
                    false
                );
            }
            return;
        }

        $group = $awsResult['AutoScalingGroups'];

        if ($this->asgExistProperty) {
            $this->project->setProperty(
                $this->asgExistProperty,
                true
            );
        }

        if ($this->outputProperty) {
            $this->project->setProperty(
                "{$this->outputProperty}.AutoScalingGroupName",
                $group[0]['AutoScalingGroupName']
            );

            $this->project->setProperty(
                "{$this->outputProperty}.AutoScalingGroupARN",
                $group[0]['AutoScalingGroupARN']
            );

            $this->project->setProperty(
                "{$this->outputProperty}.LaunchConfigurationName",
                $group[0]['LaunchConfigurationName']
            );
        }
    }
}
