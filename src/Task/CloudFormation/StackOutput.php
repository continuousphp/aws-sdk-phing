<?php
/**
 * Created by PhpStorm.
 * User: antoine
 * Date: 01/07/2014
 * Time: 17:22
 */

namespace Aws\Task\CloudFormation;

class StackOutput
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $property;

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $property
     *
     * @return $this
     */
    public function setProperty($property) {
        $this->property = $property;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperty() {
        return $this->property;
    }




}