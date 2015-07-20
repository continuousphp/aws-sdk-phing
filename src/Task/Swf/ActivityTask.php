<?php
/**
 * ActivityTask.php
 *
 * @date        07/05/2014
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        ActivityTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Swf;
use Aws\Swf\Exception\SwfException;
use Aws\Swf\Exception\UnknownResourceException;
use Aws\Swf\SwfClient;
use Aws\Task\AbstractTask;

/**
 * ActivityTask
 *
 * @package     Aws
 * @subpackage  Swf
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class ActivityTask extends AbstractTask
{
    /**
     * Activity domain
     * @var string
     */
    protected $domain;

    /**
     * Activity name
     * @var string
     */
    protected $name;

    /**
     * Activity version
     * @var string
     */
    protected $version;

    /**
     * Activity description
     * @var string
     */
    protected $description;

    /**
     * Activity default tasklist
     * @var string
     */
    protected $tasklist;

    /**
     * Activity default start to close timeout
     * @var string
     */
    protected $startToCloseTimeout;

    /**
     * Activity default heartbeat timeout
     * @var string
     */
    protected $heartbeatTimeout;

    /**
     * Activity default schedule to close timeout
     * @var string
     */
    protected $scheduleToStartTimeout;

    /**
     * Activity default schedule to close timeout
     * @var string
     */
    protected $scheduleToCloseTimeout;

    /**
     * @var SwfClient
     */
    protected $service;

    /**
     * @param  string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return the $name
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
     * @param  string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param  string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param  string $tasklist
     * @return $this
     */
    public function setTasklist($tasklist)
    {
        $this->tasklist = $tasklist;

        return $this;
    }

    /**
     * @return string
     */
    public function getTasklist()
    {
        return $this->tasklist;
    }

    /**
     * @param  string $heartbeatTimeout
     * @return $this
     */
    public function setHeartbeatTimeout($heartbeatTimeout)
    {
        $this->heartbeatTimeout = $heartbeatTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeartbeatTimeout()
    {
        return $this->heartbeatTimeout;
    }

    /**
     * @param  string $scheduleToCloseTimeout
     * @return $this
     */
    public function setScheduleToCloseTimeout($scheduleToCloseTimeout)
    {
        $this->scheduleToCloseTimeout = $scheduleToCloseTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getScheduleToCloseTimeout()
    {
        return $this->scheduleToCloseTimeout;
    }

    /**
     * @param  string $scheduleToStartTimeout
     * @return $this
     */
    public function setScheduleToStartTimeout($scheduleToStartTimeout)
    {
        $this->scheduleToStartTimeout = $scheduleToStartTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getScheduleToStartTimeout()
    {
        return $this->scheduleToStartTimeout;
    }

    /**
     * @param  string $startToCloseTimeout
     * @return $this
     */
    public function setStartToCloseTimeout($startToCloseTimeout)
    {
        $this->startToCloseTimeout = $startToCloseTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartToCloseTimeout()
    {
        return $this->startToCloseTimeout;
    }

    /**
     * @return SwfClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->createSwf();
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main(){
        
        if(is_null($this->getDomain())){
            throw new \BuildException('the domain property is required');
        }

        if(is_null($this->getName())){
            throw new \BuildException('the name property is required');
        }

        if(is_null($this->getVersion())){
            throw new \BuildException('the version property is required');
        }

        if(is_null($this->getTasklist())){
            throw new \BuildException('the tasklist property is required');
        }

        try {
            $activity = $this->getService()->describeActivityType([
                'domain' => $this->getDomain(),
                'activityType' => [
                    'name' => $this->getName(),
                    'version' => $this->getVersion()
                ]
            ]);

            if ($activity->get('typeInfo')['status'] != 'REGISTERED') {
                throw new \BuildException('the activity "' . $this->getName() . '" is no more available');
            }

            $this->log('The activity "' . $this->getName() . '" already exists');
        } catch (SwfException $e) {
            $this->log('Creating "' . $this->getName() . '" v.' . $this->getVersion() . ' activity');

            $params = [
                'domain' => $this->getDomain(),
                'name' => $this->getName(),
                'version' => $this->getVersion(),
                'description' => $this->getDescription(),
                'defaultTaskStartToCloseTimeout' => $this->getStartToCloseTimeout() ?: 'NONE',
                'defaultTaskHeartbeatTimeout' => $this->getHeartbeatTimeout() ?: 'NONE',
                'defaultTaskScheduleToStartTimeout' => $this->getScheduleToStartTimeout() ?: 'NONE',
                'defaultTaskScheduleToCloseTimeout' => $this->getScheduleToCloseTimeout() ?: 'NONE',
                'defaultTaskList' => [
                    'name' => $this->getTasklist()
                ]
            ];

            $this->getService()->registerActivityType($params);

            $this->log('"' . $this->getName() . '" v.' . $this->getVersion() . ' activity successfully created');
        }
    }

}