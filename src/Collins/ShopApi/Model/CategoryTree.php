<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class CategoryTree implements \IteratorAggregate
{
    /** @var Category[] */
    protected $categories;

    public function __construct($jsonObject)
    {
        $this->categories = [];
        $this->fromJson($jsonObject);
    }

    public function createCategory($jsonCategory)
    {
        return new Category($jsonCategory, null);
    }

    public function fromJson($jsonObject)
    {
        foreach ($jsonObject as $jsonCategory) {
            $this->categories[] = $this->createCategory($jsonCategory);
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * allows foreach iteration over the products
     * @return Iterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->categories);
    }
}