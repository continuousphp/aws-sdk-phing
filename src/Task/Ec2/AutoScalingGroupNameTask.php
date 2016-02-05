<?php
/**
 * AutoScalingGroupNameTask.php
 *
 * @date        05/02/2016
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        AutoScalingGroupNameTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Ec2;
use Aws\Task\AbstractTask;
use Aws\Ec2\Ec2Client;

/**
 * AutoScalingGroupNameTask
 *
 * @package     Aws
 * @subpackage  Ec2
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class AutoScalingGroupNameTask extends AbstractTask
{

    /**
     * Property name
     * @var string
     */
    protected $property;
    
    /**
     * Instance Id
     * @var string
     */
    protected $instanceId;

    /**
     * @var Ec2Client
     */
    protected $service;

    /**
     * @return string $property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     * @return $this
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param string $instanceId
     * @return $this
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
        return $this;
    }
    
    /**
     * @return Ec2Client
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->createEc2();
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main()
    {
        $ec2 = $this->getService();

        $instance = $ec2->describeInstances([
            'InstanceIds' => [$this->getInstanceId()]
        ]);

        $tag = array_filter(
            $instance['Reservations'][0]['Instances'][0]['Tags'],
            function ($tag) {
                return $tag['Key'] == 'aws:autoscaling:groupName';
            }
        );
        
        $this->project->setProperty($this->getProperty(), array_shift($tag)['Value']);
    }
}
