<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

class Category extends AbstractModel
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

    /** @var Category */
    protected $parent;

    /** @var Category[] */
    protected $allSubCategories;

    /** @var Category[] */
    protected $activeSubCategories;

    /** @var integer */
    protected $productCount;

    protected function __construct()
    {
        $this->allSubCategories = array();
        $this->activeSubCategories = array();
    }

    /**
     * @param object        $jsonObject  json as object tree
     * @param ModelFactoryInterface $factory
     * @param Category|null $parent
     *
     * @return static
     */
    public static function createFromJson($jsonObject, ModelFactoryInterface $factory, $parent = null)
    {
        $category = new static();

        $category->parent   = $parent;
        $category->parentId = $jsonObject->parent;
        $category->id       = $jsonObject->id;
        $category->name     = $jsonObject->name;
        $category->isActive = $jsonObject->active;
        $category->position = $jsonObject->position;

        if (isset($jsonObject->sub_categories)) {
            foreach ($jsonObject->sub_categories as $jsonSubCategory) {
                $subCategory = $factory->createCategory($jsonSubCategory, $category);
                $category->allSubCategories[] = $subCategory;
                if ($subCategory->isActive) {
                    $category->activeSubCategories[] = $subCategory;
                }
            }
        }

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
        if (!$this->parent && $this->getParentId()) {
            $this->parent = $this->getShopApi()->getCategoryManager()->getCategory($this->getParentId());

        }

        return $this->parent;
    }

    /**
     * @param bool $activeOnly
     *
     * @return Category[]
     */
    public function getSubCategories($activeOnly = self::ACTIVE_ONLY)
    {
        if ($activeOnly) {
            return $this->activeSubCategories;
        }

        return $this->allSubCategories;
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
     * Sets the parent category of this category
     *
     * @param Category $parent
     * @param bool $doAddChild
     */
    public function setParent(Category $parent, $doAddChild = false)
    {
        $this->parent = $parent;

        if ($doAddChild) {
            $parent->addChild($this);
        }
    }

    /**
     * @param Category $child
     */
    public function addChild(Category $child)
    {
        $this->allSubCategories[] = $child;

        if($child->isActive()) {
            $this->activeSubCategories[] = $child;
        }
    }

    /**
     * @param Category[] $categories
     */
    public function setSubCategories(array $categories)
    {
        $this->allSubCategories = $categories;

        foreach ($categories as $category) {
            if($category->isActive()) {
                $this->activeSubCategories[] = $category;
            }
        }
    }

    /**
     * Builds a return a category tree array
     *
     * @param Category[] categories
     * @return array
     *
     * @deprecated
     */
    public static function buildTree($categories)
    {
        $tree = array();
        foreach($categories as $category) {
            if(!self::addToTree($category, $tree)) {
                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * Used to add a single category in a category tree array
     *
     * @param Category $category
     * @param array $tree
     * @return bool true if category could be added
     *
     * @deprecated
     */
    protected static function addToTree($category, &$tree) {
        $added = false;

        foreach($tree as $key => $cat) {

            // is parent?
            if($cat->getId() == $category->getParentId()) {
                $category->setParent($cat);
                $cat->addChild($category);

                $tree[$key] = $cat;
                return true;
            }

            // is child?
            if($cat->getParentId() == $category->getId()) {
                $added = true;

                $cat->setParent($category);
                $category->addChild($cat);

                $tree[$key] = $category;
                return true;
            }

            // check children
            $subCategories = $cat->getSubCategories();

            if(count($subCategories) && self::addToTree($category, $subCategories)) {
                $cat->setSubCategories($subCategories);
                $tree[$key] = $cat;
                return true;
            }
        }

        return false;
    }
}