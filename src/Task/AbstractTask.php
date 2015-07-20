<?php
/**
 * AbstractTask.php
 *
 * @date        07/05/2014
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        AbstractTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task;
use Aws\Sdk;

/**
 * AbstractTask
 *
 * @package     Aws
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
abstract class AbstractTask extends \Task
{
    /**
     * @var Sdk
     */
    static protected $serviceLocator;

    /**
     * @return Sdk
     */
    protected function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * @param Sdk $serviceLocator
     * @return $this
     */
    protected function setServiceLocator(Sdk $serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;

        return $this;
    }
} 