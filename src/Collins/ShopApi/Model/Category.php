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

    /** @var bool */
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

    public function fromJson($jsonObject)
    {
        $this->parentId = $jsonObject->parent;
        $this->id = $jsonObject->id;
        $this->name = $jsonObject->name;
        $this->isActive = $jsonObject->active;
        $this->position = $jsonObject->position;

        foreach ($jsonObject->sub_categories as $jsonSubCategory) {
            $this->subCategories[] = new Category($jsonSubCategory, $this);
        }
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getSubCategories()
    {
        return $this->subCategories;
    }


    public function fetchProducts($limit, $offset)
    {

    }


} 