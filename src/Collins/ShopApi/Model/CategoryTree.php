<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class CategoryTree
{
    /** @var Category[] */
    protected $categories;

    public function __construct($jsonObject)
    {
        $this->categories = [];
        $this->fromJson($jsonObject);
    }

    public function createCategory($jsonCategory, $parent = null)
    {
        return new Category($jsonCategory, $parent);
    }

    public function fromJson($jsonObject)
    {
        foreach ($jsonObject as $jsonCategory) {
            $this->categories[] = new Category($jsonCategory, null);
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }
}