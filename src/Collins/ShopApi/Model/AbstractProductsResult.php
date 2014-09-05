<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

abstract class AbstractProductsResult implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var Product[] */
    protected $products;

    /** @var string */
    protected $pageHash;

    protected function __construct()
    {
        $this->products = array();
    }

    /**
     * @return string
     */
    public function getPageHash()
    {
        return $this->pageHash;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
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
    public function getIterator()
    {
        return new \ArrayIterator($this->products);
    }

    /**
     * Tests, if a Product with this id exists
     *
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->products[$offset]);
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
        return isset($this->products[$offset]) ? $this->products[$offset] : null;
    }

    /**
     * {@inheritdoc}
     *
     * throws LogicException because, it's readonly
     */
    public function offsetSet($index, $newval)
    {
        throw new LogicException('Attempting to write to an immutable array');
    }

    /**
     * {@inheritdoc}
     *
     * throws LogicException because, it's readonly
     */
    public function offsetUnset($index)
    {
        throw new LogicException('Attempting to write to an immutable array');
    }

    /**
     * Count of all fetched Products
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->products);
    }
}