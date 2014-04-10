<?php
namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

/**
 *
 */
class CategoriesResult implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var Category[] */
    protected $categories = array();

    protected $categoriesNotFound = array();

    protected function __construct()
    {
    }

    /**
     * @param $jsonObject
     * @param null $orderByIds
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson($jsonObject, $orderByIds = null, ModelFactoryInterface $factory)
    {
        $categoriesResult = new static();

        if ($orderByIds === null) {
            $orderByIds = array_keys(get_object_vars($jsonObject));
        }

        foreach ($orderByIds as $id) {
            if (!isset($jsonObject->$id) ) {
                continue;
            }

            $jsonCategory = $jsonObject->$id;
            if (isset($jsonCategory->error_code)) {
                $categoriesResult->categoriesNotFound[] = $id;
            } else {
                $categoriesResult->categories[$id] = $factory->createCategory($jsonCategory);
            }
        }

        return $categoriesResult;
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
        return $this->categoriesNotFound;
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