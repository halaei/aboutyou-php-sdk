<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Category
{
    /** @var integer */
    public $id;

    /** @var string */
    public $name;

    /** @var boolean */
    public $isActive;

    /** @var integer */
    public $position;

    public $parentId;

    /** @var Category */
    protected $parent;

    /** @var Category[] */
    protected $subCategories;

    public function __construct($jsonObject, $parent = null)
    {
        $this->subCategories = [];
        $this->parent = $parent;
        $this->fromJson($jsonObject);
    }

    public function createCategory($jsonCategory, $parent = null)
    {
        return new Category($jsonCategory, $parent);
    }

    public function fromJson($jsonObject)
    {
        $this->parentId = $jsonObject->parent;
        $this->id = $jsonObject->id;
        $this->name = $jsonObject->name;
        $this->isActive = $jsonObject->active;
        $this->position = $jsonObject->position;

        foreach ($jsonObject->sub_categories as $jsonSubCategory) {
            $this->subCategories[] = $this->createCategory($jsonSubCategory, $this);
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getSubCategories()
    {
        return $this->subCategories;
    }

    public function getBreadcrumb()
    {
        $breadcrumb = $this->parent ? $this->parent->getBreadcrumb() : [];
        $breadcrumb[] = $this;
        return $breadcrumb;
    }

    public function fetchProducts($limit, $offset)
    {

    }
} 