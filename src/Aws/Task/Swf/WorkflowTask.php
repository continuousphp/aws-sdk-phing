<?php
/**
 * WorkflowTask.php
 *
 * @date        07/05/2014
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        WorkflowTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Swf;
use Aws\Swf\Exception\UnknownResourceException;
use Aws\Swf\SwfClient;
use Aws\Task\AbstractTask;

/**
 * WorkflowTask
 *
 * @package     Aws
 * @subpackage  Swf
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class WorkflowTask extends AbstractTask
{
    /**
     * Workflow domain
     * @var string
     */
    protected $domain;

    /**
     * Workflow name
     * @var string
     */
    protected $name;

    /**
     * Workflow version
     * @var string
     */
    protected $version;

    /**
     * Workflow description
     * @var string
     */
    protected $description;

    /**
     * Workflow default tasklist
     * @var string
     */
    protected $tasklist;

    /**
     * Workflow default task start to close timeout tasklist
     * @var string
     */
    protected $taskStartToCloseTimeout;

    /**
     * Workflow default execution start to close timeout tasklist
     * @var string
     */
    protected $executionStartToCloseTimeout;

    /**
     * Workflow default child policy
     */
    protected $childPolicy;

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
     * @param  string $executionStartToCloseTimeout
     * @return $this
     */
    public function setExecutionStartToCloseTimeout($executionStartToCloseTimeout)
    {
        $this->executionStartToCloseTimeout = $executionStartToCloseTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getExecutionStartToCloseTimeout()
    {
        return $this->executionStartToCloseTimeout;
    }

    /**
     * @param  string $taskStartToCloseTimeout
     * @return $this
     */
    public function setTaskStartToCloseTimeout($taskStartToCloseTimeout)
    {
        $this->taskStartToCloseTimeout = $taskStartToCloseTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaskStartToCloseTimeout()
    {
        return $this->taskStartToCloseTimeout;
    }

    /**
     * @param  mixed $childPolicy
     * @return $this
     */
    public function setChildPolicy($childPolicy)
    {
        $this->childPolicy = $childPolicy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildPolicy()
    {
        return $this->childPolicy;
    }

    /**
     * @return SwfClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->get('Swf');
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
            $workflow = $this->getService()->describeWorkflowType([
                'domain' => $this->getDomain(),
                'workflowType' => [
                    'name' => $this->getName(),
                    'version' => $this->getVersion()
                ]
            ]);

            if ($workflow->get('typeInfo')['status'] != 'REGISTERED') {
                throw new \BuildException('the workflow "' . $this->getName() . '" is no more available');
            }

            $this->log('The workflow "' . $this->getName() . '" already exists');
        } catch (UnknownResourceException $e) {
            $this->log('Creating "' . $this->getName() . '" v.' . $this->getVersion() . ' workflow');

            $params = [
                'domain' => $this->getDomain(),
                'name' => $this->getName(),
                'version' => $this->getVersion(),
                'description' => $this->getDescription(),
                'defaultTaskStartToCloseTimeout' => $this->getTaskStartToCloseTimeout() ?: 'NONE',
                'defaultExecutionStartToCloseTimeout' => $this->getExecutionStartToCloseTimeout() ?: 'NONE',
                'defaultTaskList' => [
                    'name' => $this->getTasklist()
                ]
            ];

            if ($this->getChildPolicy()) {
                $params['defaultChildPolicy'] = $this->getChildPolicy();
            }

            $this->getService()->registerWorkflowType($params);

            $this->log('"' . $this->getName() . '" v.' . $this->getVersion() . ' workflow successfully created');
        }
    }

}