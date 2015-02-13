<?php
/**
 * @author nils.droege@aboutyou.de
 * @author christian.kilb@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;

use AboutYou\SDK\Model\CategoryManager\CategoryManagerInterface;

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


    /** @var integer */
    protected $productCount;

    /** @var CategoryManagerInterface[] */
    protected static $categoryManagers;

    /** @var  string */
    protected $categoryManagerClass;

    /**
     * @param object        $jsonObject  json as object tree
     * @param CategoryManagerInterface $categoryManager
     *
     * @return static
     */
    public static function createFromJson($jsonObject, CategoryManagerInterface $categoryManager)
    {
        $category = new static();

        $category->parentId = $jsonObject->parent;
        $category->id       = $jsonObject->id;
        $category->name     = $jsonObject->name;
        $category->isActive = $jsonObject->active;
        $category->position = $jsonObject->position;

        // Don't store categoryManager as attribute of the instance
        // because it would bloat the cache when the categories
        // get saved serialized
        $category->categoryManagerClass = get_class($categoryManager);

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

        return $this->getCategoryManager()->getCategory($this->getParentId());
    }

    /**
     * @param bool $activeOnly
     *
     * @return Category[]
     */
    public function getSubCategories($activeOnly = self::ACTIVE_ONLY)
    {
        $subCategories = $this->getCategoryManager()->getSubCategories($this->id, self::ALL);

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

    /**
     * @return CategoryManagerInterface
     */
    public function getCategoryManager()
    {
        $class = $this->categoryManagerClass;
        $categoryManager = self::$categoryManagers[$class];

        if (!$categoryManager) {
            self::$categoryManagers[$this->categoryManagerClass] = new $class;
        }

        return  self::$categoryManagers[$class];
    }
}