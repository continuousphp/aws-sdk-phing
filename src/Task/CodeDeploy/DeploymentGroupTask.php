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
use Aws\CodeDeploy\CodeDeployClient;
use Aws\CodeDeploy\Exception\CodeDeployException;
use Aws\Task\AbstractTask;

/**
 * DeploymentGroupTask
 *
 * @package     Aws
 * @subpackage  CodeDeploy
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
     * @var
     */
    protected $application;

    /**
     * Update on conflict
     */
    protected $updateOnConflict = false;

    /**
     * @var string
     */
    protected $deploymentConfigName;

    /**
     * @var string
     */
    protected $serviceRole;

    /**
     * @var CodeDeployClient
     */
    protected $service;

    /**
     * @var array
     */
    protected $autoScalingGroups = [];

    /**
     * @var array
     */
    protected $ec2TagFilters = [];

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
     * @return string
     */
    public function getDeploymentConfigName()
    {
        return $this->deploymentConfigName;
    }

    /**
     * @param string $deploymentConfigName
     * @return $this
     */
    public function setDeploymentConfigName($deploymentConfigName)
    {
        $this->deploymentConfigName = $deploymentConfigName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceRole()
    {
        return $this->serviceRole;
    }

    /**
     * @param string $serviceRole
     * @return $this
     */
    public function setServiceRole($serviceRole)
    {
        $this->serviceRole = $serviceRole;
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
     * Called by phing for each <autoScalingGroup/> tag
     * @return AutoScalingGroup
     */
    public function createAutoScalingGroup()
    {
        $group = new AutoScalingGroup();
        $this->autoScalingGroups[] = $group;
        return $group;
    }

    /**
     * @return array
     */
    protected function getAutoScalingGroups()
    {
        return array_map(function (AutoScalingGroup $group) {
            return (string)$group;
        }, $this->autoScalingGroups);
    }

    /**
     * Called by phing for each <ec2TagFilter/> tag
     * @return Ec2TagFilter
     */
    public function createEc2TagFilter()
    {
        $filter = new Ec2TagFilter();
        $this->ec2TagFilters[] = $filter;
        return $filter;
    }

    /**
     * @return array
     */
    protected function getEc2TagFilters()
    {
        return array_map(function (Ec2TagFilter $filter) {
            return $filter->toArray();
        }, $this->ec2TagFilters);
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
            'currentDeploymentGroupName' => $this->getName(),
            'serviceRoleArn' => $this->getServiceRole()
        ];
        
        if ($this->autoScalingGroups) {
            $config['autoScalingGroups'] = $this->getAutoScalingGroups();
        }
        
        if ($this->ec2TagFilters) {
            $config['ec2TagFilters'] = $this->getEc2TagFilters();
        }

        if ($this->getDeploymentConfigName()) {
            $config['deploymentConfigName'] = $this->getDeploymentConfigName();
        }

        try {
            $codeDeploy
                ->getDeploymentGroup([
                    'applicationName' => $this->getApplication(),
                    'deploymentGroupName' => $this->getName()
                ]);
            // update
            if ($this->updateOnConflict) {
                $config['currentDeploymentGroupName'] = $this->getName();
                $codeDeploy->updateDeploymentGroup($config);
                $this->log('Deployment Group [' . $this->getName() . '] successfully updated.');
            } else {
                throw new \BuildException('Deployment Group [' . $this->getName() . '] already exists!');
            }
        } catch (CodeDeployException $e) {
            switch ($e->getAwsErrorCode()) {
                case 'ApplicationDoesNotExistException':
                    $codeDeploy->createApplication([
                        'applicationName' => $config['applicationName']
                    ]);
                    $this->log('Application [' . $config['applicationName'] . '] successfully created.');
                case 'DeploymentGroupDoesNotExistException':
                    // create deployment group
                    $config['deploymentGroupName'] = $this->getName();
                    $codeDeploy->createDeploymentGroup($config);
                    $this->log('Deployment Group [' . $this->getName() . '] successfully created.');
                    break;
                default:
                    throw $e;
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
