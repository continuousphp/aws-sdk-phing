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
     * Template Path
     * @var string
     */
    protected $templatePath;

    /**
     * Template URL
     * @var string
     */
    protected $templateUrl;

    /**
     * Update on conflict
     */
    protected $updateOnConflict = false;

    /**
     * Give permission to perform operations on IAM
     * @var string
     */
    protected $capabilities;

    /**
     * AWS ARN of Role used to perform cloudformation operations
     * @var string
     */
    protected $roleARN;

    /**
     * Stack params array
     * @var StackParam[]
     */
    protected $params = [];

    /**
     * Stack tags array
     * @var StackTag[]
     */
    protected $tags = [];

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
     * @var \DateTimeImmutable
     */
    protected $datetimeTaskStart;

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
     * @return string
     */
    public function getTemplateUrl()
    {
        return $this->templateUrl;
    }

    /**
     * @param string $templateUrl
     * @return $this
     */
    public function setTemplateUrl($templateUrl)
    {
        $this->templateUrl = $templateUrl;
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
     * @return mixed
     */
    public function getRoleARN()
    {
        return $this->roleARN;
    }

    /**
     * @param mixed $arn
     */
    public function setRoleARN($arn)
    {
        $this->roleARN = $arn;
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
     * Called by phing for each <tag/> tag
     * @return StackTag
     */
    public function createTag()
    {
        $tag = new StackTag();
        $this->tags[] = $tag;
        return $tag;
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
     * Return the array representation of the tags
     * @return array
     */
    public function getTagsArray()
    {
        $result = [];

        foreach($this->tags as $tag) {
            $result[] = $tag->toArray();
        }

        return $result;
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

    public function stackProperties()
    {
        $stackProperties = [
            'StackName'  => $this->getName(),
            'Parameters' => $this->getParamsArray(),
            'Tags'       => $this->getTagsArray(),
        ];

        if ($template = $this->getTemplatePath()) {
            $stackProperties['TemplateBody'] = file_get_contents($template);
        } else {
            $stackProperties['TemplateURL'] = $this->getTemplateUrl();
        }

        if ($this->getCapabilities()) {
            $stackProperties['Capabilities'] = explode(',', $this->getCapabilities());
        }

        if ($roleARN = $this->getRoleARN()) {
            $stackProperties['RoleARN'] = $roleARN;
        }

        return $stackProperties;
    }

    /**
     * Task entry point
     */
    public function main()
    {
        $this->validate();
        $this->datetimeTaskStart = new \DateTimeImmutable();

        if ($this->describeMainStack()) {
            $this->updateStacks();
        } else {
            $this->creationStacks();
        }

        while (!$this->stackIsReady()) {
            sleep(3);
            if (empty($this->events)) {
                $this->log("Waiting for stack provisioning...");
            }
        }

        $stacks = $this->describeMainStack($e);

        if (null === $stacks) {
            throw $e;
        }

        $outputLog = '';

        if ($stacks[0]['Outputs']) {
            foreach ($stacks[0]['Outputs'] as $row) {
                $outputLog .= PHP_EOL . $row['OutputKey'] . ': ' . $row['OutputValue'];
                if ($output = $this->getOutput($row['OutputKey'])) {
                    /** @var StackOutput $output */
                    $this->project->setProperty($output->getProperty(), $row['OutputValue']);
                }
            }
        }

        $this->log($outputLog);
    }

    /**
     * Log the main stack events
     * Return true when the cloudformation is complete
     * @return bool
     */
    protected function stackIsReady()
    {
        $stacks = $this->describeMainStack();

        if (null === $stacks) {
            return false;
        }

        switch ($stacks[0]['StackStatus']) {
            case 'CREATE_COMPLETE':
            case 'UPDATE_COMPLETE':
            case 'UPDATE_COMPLETE_CLEANUP_IN_PROGRESS':
                return true;
            case 'UPDATE_IN_PROGRESS':
            case 'CREATE_IN_PROGRESS':
                $this->logRecentEvents();
            case '':
                return false;
            default:
                sleep(3);
                $this->logRecentEvents();
                throw new \BuildException('Failed to run stack ' . $this->getName() . ' (' . $stacks[0]['StackStatus'] . ') !');
        }
    }

    /**
     * Validate attributes
     *
     * @throws \BuildException
     */
    protected function validate() {

        if (!$this->getTemplatePath() && !$this->getTemplateUrl()) {
            throw new \BuildException('You must set the templatePath or templateUrl attribute.');
        }

        if (!$this->getName()) {
            throw new \BuildException('You must set the name attribute.');
        }

    }

    /**
     * Return the Main Stack result or null if cannot be found
     *
     * @param CloudFormationException|null $cloudFormationException
     * @return null
     */
    private function describeMainStack(CloudFormationException & $cloudFormationException = null)
    {
        $cloudFormation = $this->getService();

        try {
            $stack = $cloudFormation->describeStacks([
                'StackName' => $this->getName(),
            ]);

            return $stack['Stacks'];
        } catch (CloudFormationException $e) {
            $cloudFormationException = $e;
            $message = $e->getMessage();
        }

        if (1 === preg_match('/AccessDenied|403 Forbidden/i', $message)) {
            throw new \BuildException('You are not authorized to perform describeStacks on [' . $this->getName() . ']. Check the credentials and AWS policy');
        }

        if (1 ===  preg_match('/Could not resolve host/i', $message)) {
            throw new \BuildException("Could not resolve AWS host, thanks to check the AWS_REGION settings or connectivity");
        }

        return null;
    }

    /**
     * Create stacks in cloudformation
     * @throws \Exception
     */
    private function creationStacks()
    {
        $cloudFormation = $this->getService();

        if (true !== $this->getUpdateOnConflict()) {
            throw new \BuildException('Stack ' . $this->getName() . ' not exist. Creation stack was skip due to property updateOnConflict set to false');
        }

        try {
            $this->log('Create stacks...');
            $cloudFormation->createStack($this->stackProperties());
        } catch (CloudFormationException $e) {
            if ($e->getAwsErrorCode() !== 'AlreadyExistsException') {
                throw $e;
            } else {
                $this->log('stack [' . $this->getName() . '] creation already in progress.');
            }
        }
    }

    /**
     * Update stacks in cloudformation
     * @throws \BuildException
     */
    private function updateStacks()
    {
        $cloudFormation = $this->getService();

        try {
            $this->log('Update stacks...');
            $cloudFormation->updateStack($this->stackProperties());
        } catch (CloudFormationException $e) {
            if (1 === preg_match('/No updates are to be performed/i', $e->getMessage())) {
                $this->log('No updates are to be performed.');
                return;
            }

            throw new \BuildException("AwsErrorCode [{$e->getAwsErrorCode()}] Message : " . $e->getMessage());
        }
    }

    /**
     * Log the recent stack events
     * @note the log already process by this method will be ignored
     */
    private function logRecentEvents()
    {
        $events = $this->getService()
            ->describeStackEvents([
                'StackName' => $this->getName()
            ]);
        $events = array_column(array_reverse($events['StackEvents']), null, 'EventId');

        foreach(array_diff_assoc($events, $this->events) as $event) {

            $timestamp = $event['Timestamp'];

            if ($timestamp < $this->datetimeTaskStart) {
                continue;
            }

            $this->log($timestamp . ': '
                . $event['ResourceType'] . ' > ' . $event['LogicalResourceId']
                . ' (' . $event['ResourceStatus'] . ')'
            );

            if ($event['ResourceStatusReason']) {
                $this->log($event['ResourceStatusReason']);
            }
        }

        $this->events = $events;
    }
}
