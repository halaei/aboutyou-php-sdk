<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class CategoriesResult extends AbstractModel implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var Category[] */
    protected $categories = [];

    protected $categoriesNotFound = [];

    public function __construct($jsonObject, $orderByIds = null)
    {
        $this->fromJson($jsonObject, $orderByIds);
    }

    public function fromJson($jsonObject, $orderByIds = null)
    {
        if ($orderByIds === null) {
            $orderByIds = array_keys(get_object_vars($jsonObject));
        }

        $factory = $this->getModelFactory();

        foreach ($orderByIds as $id) {
            if (!isset($jsonObject->$id) ) {
                continue;
            }

            $jsonCategory = $jsonObject->$id;
            if (isset($jsonCategory->error_code)) {
                $this->categoriesNotFound[] = $id;
            } else {
                $this->categories[$id] = $factory->createCategory($jsonCategory);
            }
        }
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