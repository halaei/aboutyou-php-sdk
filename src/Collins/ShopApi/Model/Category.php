<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
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

    /**
     * @param object        $jsonObject  json as object tree
     * @param Category|null $parent
     */
    public function __construct($jsonObject, $parent = null)
    {
        $this->allSubCategories = [];
        $this->activeSubCategories = [];
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
     * @return Category|null
     */
    public function getParent()
    {
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
        $breadcrumb = $this->parent ? $this->parent->getBreadcrumb() : [];
        $breadcrumb[] = $this;

        return $breadcrumb;
    }
}