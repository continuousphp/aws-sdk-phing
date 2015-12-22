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
use Aws\CloudFormation\Exception\CloudFormationException;
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
     * Stack name
     * @var string
     */
    protected $templatePath;

    /**
     * Update on conflict
     */
    protected $updateOnConflict = false;

    /**
     * @var string
     */
    protected $capabilities;

    /**
     * Stack params array
     * @var StackParam[]
     */
    protected $params = [];

    /**
     * Stack params array
     * @var StackOutput[]
     */
    protected $outputs = [];

    /**
     * @var CloudFormationClient
     */
    protected $service;

    /**
     * @var array
     */
    protected $events = [];

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
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
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
     * @return mixed
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * @param mixed $capabilities
     */
    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * Called by phing for each <param/> tag
     * @return StackParam
     */
    public function createParam()
    {
        $param = new StackParam();
        $this->params[] = $param;
        return $param;
    }

    /**
     * Called by phing for each <output/> tag
     * @return StackOutput
     */
    public function createOutput()
    {
        $output = new StackOutput();
        $this->outputs[] = $output;
        return $output;
    }

    /**
     * @param string $name
     * @return StackOutput
     */
    public function getOutput($name)
    {
        foreach ($this->outputs as $output) {
            if ($output->getName()==$name) {
                return $output;
            }
        }
    }

    /**
     * Return the array representation of the params
     * @return array
     */
    public function getParamsArray()
    {
        $result = [];

        foreach($this->params as $param) {
            $result[] = $param->toArray();
        }

        return $result;
    }

    /**
     * @return CloudFormationClient
     */
    public function getService()
    {
        if (is_null($this->service)) {
            $this->service = $this->getServiceLocator()->createCloudFormation();
        }

        return $this->service;
    }

    /**
     * Task entry point
     */
    public function main()
    {
        $this->validate();

        $cloudFormation = $this->getService();

        $stackProperties = [
            'StackName' => $this->getName(),
            'TemplateBody' => file_get_contents($this->getTemplatePath()),
            'Parameters'    => $this->getParamsArray()
        ];

        if ($this->getCapabilities()) {
            $stackProperties['Capabilities'] = explode(',', $this->getCapabilities());
        }

        try {
            $cloudFormation->describeStacks([
                'StackName' => $this->getName()
            ]);
            // update
            $cloudFormation->updateStack($stackProperties);
        } catch (CloudFormationException $e) {
            if (!preg_match('/No updates are to be performed./', $e->getMessage())) {
                if ($this->getUpdateOnConflict()) {
                    try {
                        $cloudFormation->createStack($stackProperties);
                    } catch (CloudFormationException $e) {
                        if ($e->getAwsErrorCode()!='AlreadyExistsException') {
                            throw $e;
                        } else {
                            $this->log('stack [' . $this->getName() . '] creation already in progress.');
                        }
                    }
                } else {
                    throw new \BuildException('Stack ' . $this->getName() . ' already exists!');
                }
            }
        }

        while (!$this->stackIsReady()) {
            sleep(3);
            if (empty($this->events)) {
                $this->log("Waiting for stack provisioning...");
            }
        }

        $stacks = $cloudFormation->describeStacks([
            'StackName' => $this->getName()
        ]);

        $outputLog = '';
        if ($stacks['Stacks'][0]['Outputs']) {
            foreach ($stacks['Stacks'][0]['Outputs'] as $row) {
                $outputLog.= PHP_EOL . $row['OutputKey'] . ': ' . $row['OutputValue'];
                if ($output = $this->getOutput($row['OutputKey'])) {
                    /** @var StackOutput $output */
                    $this->project->setProperty($output->getProperty(), $row['OutputValue']);
                }
            }
        }
        $this->log($outputLog);
    }

    protected function stackIsReady()
    {
        try {
            $stack = $this->getService()
                ->describeStacks([
                    'StackName' => $this->getName()
                ]);

            switch ($stack['Stacks'][0]['StackStatus']) {
                case 'CREATE_COMPLETE':
                case 'UPDATE_COMPLETE':
                case 'UPDATE_COMPLETE_CLEANUP_IN_PROGRESS':
                    return true;
                case 'UPDATE_IN_PROGRESS':
                case 'CREATE_IN_PROGRESS':
                    $events = $this->getService()
                        ->describeStackEvents([
                            'StackName' => $this->getName()
                        ]);
                    $events = array_column(array_reverse($events['StackEvents']), null, 'EventId');
                    foreach(array_diff_assoc($events, $this->events) as $event) {
                        $this->log($event['Timestamp'] . ': ' . $event['ResourceType'] . ' (' . $event['ResourceStatus'] . ')');
                    }
                    $this->events = $events;
                case '':
                    return false;
                default:
                    throw new \BuildException('Failed to run stack ' . $this->getName() . ' (' . $stack['Stacks'][0]['StackStatus'] . ') !');
            }
        } catch (CloudFormationException $e) {
            return false;
        }
    }

    /**
     * Validate attributes
     *
     * @throws \BuildException
     */
    protected function validate() {

        if(!$this->getTemplatePath()) {
            throw new \BuildException('You must set the template-path attribute.');
        }

        if(!$this->getName()) {
            throw new \BuildException('You must set the name attribute.');
        }

    }

}
