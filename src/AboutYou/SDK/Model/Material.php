<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;

class Material
{
    /** @var Composition[] */
    protected $compositions;

    /** @var string */
    protected $name;

    /** @var string|null */
    protected $type;

    public function __construct($name, array $compositions, $type = null)
    {
        $this->name         = $name;
        $this->compositions = $compositions;
        $this->type         = $type;
    }

    /**
     * @return Composition[]
     */
    public function getCompositions()
    {
        return $this->compositions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }
} 