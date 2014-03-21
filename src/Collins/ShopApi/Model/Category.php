<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


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

    /**
     * @param object        $jsonObject  json as object tree
     * @param Category|null $parent
     */
    public function __construct($jsonObject, $parent = null)
    {
        $this->allSubCategories = array();
        $this->activeSubCategories = array();
        $this->parent = $parent;
        $this->fromJson($jsonObject);
    }

    public function fromJson($jsonObject)
    {
        $this->parentId = $jsonObject->parent;
        $this->id       = $jsonObject->id;
        $this->name     = $jsonObject->name;
        $this->isActive = $jsonObject->active;
        $this->position = $jsonObject->position;

        if (isset($jsonObject->sub_categories)) {
            $factory = $this->getModelFactory();

            foreach ($jsonObject->sub_categories as $jsonSubCategory) {
                $category = $factory->createCategory($jsonSubCategory, $this);
                $this->allSubCategories[] = $category;
                if ($category->isActive) {
                    $this->activeSubCategories[] = $category;
                }
            }
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
    public function isActive()
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
        if($this->parent) {
            return $this->parent;
        }

        if(!$this->getParentId()) {
            return null;
        }

        $parents = $this->getShopApi()->fetchCategoriesByIds(array($this->getParentId()))->getCategories();
        if(count($parents)) {
            $array_parents = array_values($parents); 
            $this->parent = $array_parents[0];
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
        $breadcrumb = $this->parent ? $this->parent->getBreadcrumb() : array();
        $breadcrumb[] = $this;

        return $breadcrumb;
    }

    /**
     * Sets the parent category of this category
     * @return void
     */
    public function setParent(Category $parent)
    {
        $this->parent = $parent;
    }

    public function addChild(Category $child)
    {
        $this->allSubCategories[] = $child;

        if($child->isActive()) {
            $this->activeSubCategories[] = $child;
        }
    }

    public function setSubCategories($categories)
    {
        $this->allSubCategories = $categories;

        foreach($categories as $category) {
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