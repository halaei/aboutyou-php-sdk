<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Model\CategoryManager\CategoryManagerInterface;

class Category
{
    const ALL = false;
    const ACTIVE_ONLY = true;

    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    /** @var boolean */
    protected  $isActive;

    /** @var integer */
    protected $position;

    /** @var Category */
    protected $parentId;

    /** @var CategoryManagerInterface */
    protected $categoryManager;

    /** @var integer */
    protected $productCount;

    protected function __construct()
    {
    }

    /**
     * @param object        $jsonObject  json as object tree
     * @param CategoryManagerInterface $categoryManager
     *
     * @return static
     */
    public static function createFromJson($jsonObject, CategoryManagerInterface $categoryManager)
    {
        $category = new static();

        $category->categoryManager   = $categoryManager;
        $category->parentId = $jsonObject->parent;
        $category->id       = $jsonObject->id;
        $category->name     = $jsonObject->name;
        $category->isActive = $jsonObject->active;
        $category->position = $jsonObject->position;

        return $category;
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
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isPathActive()
    {
        $parent = $this->getParent();

        return $this->isActive && ($parent === null || $parent->isPathActive());
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

    /**
     * @param integer $productCount
     */
    public function setProductCount($productCount)
    {
        $this->productCount = $productCount;
    }

    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @return Category|null
     */
    public function getParent()
    {
        if (!$this->getParentId()) {
            return null;
        }

        return $this->categoryManager->getCategory($this->getParentId());
    }

    /**
     * @param bool $activeOnly
     *
     * @return Category[]
     */
    public function getSubCategories($activeOnly = self::ACTIVE_ONLY)
    {
        $subCategories = array_values($this->categoryManager->getSubCategories($this->id));

        if ($activeOnly === self::ALL) {
            return $subCategories;
        }

        return array_filter($subCategories, function (Category $subCategory) {
            return $subCategory->isActive();
        });
    }

    /**
     * @return Category[]
     */
    public function getBreadcrumb()
    {
        $breadcrumb = $this->getParent() ? $this->getParent()->getBreadcrumb() : array();
        $breadcrumb[] = $this;

        return $breadcrumb;
    }
}