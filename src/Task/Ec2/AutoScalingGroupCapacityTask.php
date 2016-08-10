<?php
/**
 * AutoScalingGroupCapacityTask.php
 *
 * @date        05/02/2016
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        AutoScalingGroupCapacityTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Ec2;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Task\AbstractTask;

/**
 * AutoScalingGroupCapacityTask
 *
 * @package     Aws
 * @subpackage  Ec2
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class AutoScalingGroupCapacityTask extends AbstractTask
{
    
    protected $min;
    
    protected $max;
    
    protected $desired;
    
    protected $name;
    
    protected $current;
    
    /**
     * @var Ec2Client
     */
    protected $service;

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     * @return AutoScalingGroupCapacityTask
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     * @return AutoScalingGroupCapacityTask
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDesired()
    {
        return $this->desired;
    }

    /**
     * @param mixed $desired
     * @return AutoScalingGroupCapacityTask
     */
    public function setDesired($desired)
    {
        $this->desired = $desired;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return AutoScalingGroupCapacityTask
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
        if (!$this->getName()) {
            throw new \BuildException('No autoscaling group provided!');
        }
        
        $config = [
            'AutoScalingGroupName' => $this->getName()
        ];

        if (!is_null($this->getDesired())) {
            $config['DesiredCapacity'] = $this->getDesired();
        }
        
        if (!is_null($this->getMin())) {
            $config['MinSize'] = $this->getMin();
        }
        
        if (!is_null($this->getMax())) {
            $config['MaxSize'] = $this->getMax();
        }

        $autoscaling = $this->getService();
        
        $autoscaling->updateAutoScalingGroup($config);
        
        if (!is_null($this->getDesired())) {
            while (!$this->groupIsReady()) {
                sleep(3);
                $this->log("Waiting to meet desired capacity (" . $this->getCurrent() . " on " . $this->getDesired() . " available)...");
            }
        }
    }
    
    protected function getCurrent()
    {
        return $this->current;
    }
    
    protected function groupIsReady()
    {
        $details = $this->getService()->describeAutoScalingInstances();
        
        $instances = array_filter(
            $details['AutoScalingInstances'],
            function ($instance) {
                return $instance['AutoScalingGroupName'] == $this->getName()
                    && $instance['LifecycleState'] == 'InService';
            }
        );
        
        $this->current = count($instances);
        
        return $this->current == $this->getDesired();
    }
}
