<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

class CategoryTree implements \IteratorAggregate, \Countable
{
    /** @var Category[] */
    protected $allCategories;

    /** @var Category[] */
    protected $activeCategories;

    public function __construct($jsonObject, ModelFactoryInterface $factory)
    {
        $this->allCategories = array();
        $this->activeCategories = array();
        $this->fromJson($jsonObject, $factory);
    }

    public function fromJson($jsonObject, ModelFactoryInterface $factory)
    {
        foreach ($jsonObject as $jsonCategory) {
            $category = $factory->createCategory($jsonCategory);
            $this->allCategories[] = $category;
            if ($category->isActive()) {
                $this->activeCategories[] = $category;
            }
        }
    }

    /**
     * @param bool $activeOnly if true, then only active categories will returned, otherwise all categories
     *
     * @return array|Category[]
     */
    public function getCategories($activeOnly = true)
    {
        if ($activeOnly) {
            return $this->activeCategories;
        }
        return $this->allCategories;
    }

    /**
     * allows foreach iteration on active top categories
     *
     * {@inheritdoc}
     *
     * @return Iterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->activeCategories);
    }

    /**
     * Count of the sub categories
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->allCategories);
    }
}