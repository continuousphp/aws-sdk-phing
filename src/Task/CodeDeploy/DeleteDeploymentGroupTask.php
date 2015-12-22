<?php
/**
 * DeleteDeploymentGroupTask.php
 *
 * @date        16/07/2015
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        DeleteDeploymentGroupTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task\CodeDeploy;
use Aws\CodeDeploy\CodeDeployClient;
use Aws\CodeDeploy\Exception\CodeDeployException;
use Aws\Task\AbstractTask;

/**
 * DeleteDeploymentGroupTask
 *
 * @package     Aws
 * @subpackage  CodeDeploy
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class DeleteDeploymentGroupTask extends AbstractTask
{

    /**
     * Stack name
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $application;

    /**
     * @var string
     */
    protected $serviceRole;

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
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param mixed $application
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;
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

        $config = [
            'applicationName' => $this->getApplication(),
            'DeploymentGroupName' => $this->getName()
        ];
        
        try {
            $codeDeploy
                ->deleteDeploymentGroup([
                    'applicationName' => $this->getApplication(),
                    'deploymentGroupName' => $this->getName()
                ]);
        } catch (CodeDeployException $e) {
            if ($e->getAwsErrorCode() == 'DeploymentGroupDoesNotExistException') {
                $this->log('Deployment Group [' . $this->getName() . '] does not exist.');
            }
        }
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

        if(!$this->getApplication()) {
            throw new \BuildException('You must set the application attribute.');
        }

    }

}
