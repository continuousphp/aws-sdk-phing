<?php
/**
 * ConfigTask.php
 *
 * @date        07/05/2014
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @copyright   Copyright (c) 2014 continuousphp (http://continuousphp.com)
 * @file        ConfigTask.php
 * @link        http://github.com/continuousphp/aws-sdk-phing for the canonical source repository
 * @license     http://opensource.org/licenses/MIT MIT License
 */

namespace Aws\Task;
use Aws\Sdk;

/**
 * ConfigTask
 *
 * @package     Aws
 * @author      Frederic Dewinne <frederic@continuousphp.com>
 * @license     http://opensource.org/licenses/MIT MIT License
 */
class ConfigTask extends AbstractTask
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $profile;

    /**
     * @var string
     */
    protected $region;

    /**
     * @param  string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  string $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param  string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param string $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }
    
    /**
     * Task entry point
     */
    public function main()
    {
        $config = [
            'version'  => 'latest'
        ];

        if ($this->getRegion()) {
            $config['region'] = $this->getRegion();
        }

        if ($this->getKey() && $this->getSecret()) {
            $config['credentials'] = [
                'key' => $this->getKey(),
                'secret' => $this->getSecret(),
            ];
        } elseif ($this->getProfile()) {
            $config['profile'] = $this->getProfile();
        }

        $this->setServiceLocator(new Sdk($config));
    }
} 
