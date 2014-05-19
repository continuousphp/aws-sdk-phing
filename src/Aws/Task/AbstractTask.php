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
use Aws\Common\Aws;

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
     * @var Aws
     */
    static protected $serviceLocator;

    /**
     * @return Aws
     */
    protected function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * @param Aws $serviceLocator
     * @return $this
     */
    protected function setServiceLocator(Aws $serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;

        return $this;
    }
} 