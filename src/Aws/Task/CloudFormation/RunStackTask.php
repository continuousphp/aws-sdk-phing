<?php
/**
 * RunStackTask.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        RunStackTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CloudFormation;
use Aws\CloudFormation\CloudFormationClient;
use Aws\Task\AbstractTask;

/**
 * RunStackTask
 *
 * @package     Aws
 * @subpackage  CloudFormation
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class RunStackTask extends AbstractTask
{
    
    /**
     * Stack name
     * @var string
     */
    protected $name;

    /**
     * @var CloudFormationClient
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
     * @return CloudFormationClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->get('CloudFormation');
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main(){
        
    }

}