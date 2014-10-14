<?php
namespace Collins\ShopApi\Model;

use Collins\ShopApi\Model\CategoryManager\CategoryManagerInterface;

/**
 *
 */
class CategoriesResult implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var CategoryManagerInterface */
    private $categories;

    private $ids;

    public function __construct(CategoryManagerInterface $categoryManager, $ids)
    {
        $this->ids = $ids;
        $this->categories = $categoryManager->getCategories($ids, Category::ALL);
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array of product ids
     */
    public function getCategoriesNotFound()
    {
        $idsFound = array_keys($this->categories);

        $idsNotFound = array_diff($this->ids, $idsFound);

        return array_values($idsNotFound);
    }

    /*
     * Interface implementations
     */

    /**
     * allows foreach iteration over the products
     *
     * {@inheritdoc}
     *
     * @return \Iterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->categories);
    }

    /**
     * Tests, if a Product with this id exists
     *
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->categories[$offset]);
    }

    /**
     * Returns the Product with that id
     *
     * {@inheritdoc}
     *
     * @return Product
     */
    public function offsetGet($offset)
    {
        return isset($this->categories[$offset]) ? $this->categories[$offset] : null;
    }

    /**
     * {@inheritdoc}
     *
     * throws LogicException because, it's readonly
     */
    public function offsetSet($index, $newval) {
        throw new \LogicException('Attempting to write to an immutable array');
    }

    /**
     * {@inheritdoc}
     *
     * throws LogicException because, it's readonly
     */
    public function offsetUnset($index) {
        throw new \LogicException('Attempting to write to an immutable array');
    }

    /**
     * Count of all fetched Products
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->categories);
    }
}