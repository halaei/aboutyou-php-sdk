<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;

class Composition
{
    /** @var string */
    protected $name;

    /** @var float */
    protected $percentage;

    public function __construct($name, $percentage)
    {
        $this->name = $name;
        $this->percentage = $percentage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getPercentage()
    {
        return $this->percentage;
    }
}