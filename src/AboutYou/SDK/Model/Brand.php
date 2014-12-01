<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;

class Brand
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    protected function __construct()
    {
    }

    /**
     * @param object        $jsonObject  json as object tree
     * @param CategoryManagerInterface $categoryManager
     *
     * @return static
     */
    public static function createFromJson($jsonObject)
    {
        $brand = new static();

        $brand->id       = $jsonObject->id;
        $brand->name     = $jsonObject->name;

        return $brand;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return integer
     */
    public function getName()
    {
        return $this->name;
    }
}