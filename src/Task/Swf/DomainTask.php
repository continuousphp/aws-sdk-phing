<?php
/**
 * DomainTask.php
 *
 * @date        07/05/2014
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        DomainTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\Swf;
use Aws\Swf\Exception\SwfException;
use Aws\Swf\Exception\UnknownResourceException;
use Aws\Swf\SwfClient;
use Aws\Task\AbstractTask;

/**
 * DomainTask
 *
 * @package     Aws
 * @subpackage  Swf
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class DomainTask extends AbstractTask
{
    
    /**
     * Domain name
     * @var string
     */
    protected $name;

    /**
     * Domain description
     * @var string
     */
    protected $description;

    /**
     * Domain retention
     * @var string
     */
    protected $retention=30;

    /**
     * @var SwfClient
     */
    protected $service;

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
     * @param  string $retention
     * @return $this
     */
    public function setRetention($retention)
    {
        $this->retention = $retention;

        return $this;
    }

    /**
     * @return string
     */
    public function getRetention()
    {
        return $this->retention;
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
        
        if(is_null($this->getName())){
            throw new \BuildException('the name property is required');
        }

        if(is_null($this->getRetention())){
            throw new \BuildException('the retention property is required');
        }

        try {
            $domain = $this->getService()->describeDomain([
                'name' => $this->getName()
            ]);

            if ($domain->get('domainInfo')['status'] != 'REGISTERED') {
                throw new \BuildException('the domain "' . $this->getName() . '" is no more available');
            }

            $this->log('The domain "' . $this->getName() . '" already exists');
        } catch (SwfException $e) {
            $this->log('Creating "' . $this->getName() . '" domain');

            $this->getService()->registerDomain([
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'workflowExecutionRetentionPeriodInDays' => $this->getRetention() ?: 'NONE'
            ]);

            $this->log('"' . $this->getName() . '" domain successfully created');
        }
    }

}