<?php
/**
 * DeploymentGroupTask.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        DeploymentGroupTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CodeDeploy;
use Aws\CloudFormation\Exception\CloudFormationException;
use Aws\CodeDeploy\CodeDeployClient;
use Aws\Task\AbstractTask;

/**
 * DeploymentGroupTask
 *
 * @package     Aws
 * @subpackage  CloudFormation
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class DeploymentGroupTask extends AbstractTask
{

    /**
     * Stack name
     * @var string
     */
    protected $name;

    /**
     * Update on conflict
     */
    protected $updateOnConflict = false;

    /**
     * @var CodeDeployClient
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
     * @return mixed
     */
    public function getUpdateOnConflict()
    {
        return $this->updateOnConflict;
    }

    /**
     * @param mixed $updateOnConflict
     * @return $this
     */
    public function setUpdateOnConflict($updateOnConflict)
    {
        $this->updateOnConflict = $updateOnConflict;
        return $this;
    }

    /**
     * @return CodeDeployClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->createCodeDeploy();
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main()
    {
        $this->validate();

        $codeDeploy = $this->getService();
    }

    /**
     * Validate attributes
     *
     * @throws \BuildException
     */
    protected function validate()
    {

        if(!$this->getName()) {
            throw new \BuildException('You must set the name attribute.');
        }

    }

}
