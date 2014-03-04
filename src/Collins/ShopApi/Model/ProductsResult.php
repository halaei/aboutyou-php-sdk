<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class ProductsResult extends AbstractModel implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var Product[] */
    protected $products;

    /** @var string */
    protected $pageHash;

    protected $productsNotFound = [];

    public function __construct($jsonObject)
    {
        $this->products = [];
        $this->fromJson($jsonObject);
    }

    public function fromJson($jsonObject)
    {
        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        $factory = $this->getModelFactory();

        if (isset($jsonObject->ids)) {
            foreach ($jsonObject->ids as $key => $jsonProduct) {
                if (isset($jsonProduct->error_code)) {
                    $this->productsNotFound[] = $key;
                    continue;
                }
                $this->products[$key] = $factory->createProduct($jsonProduct);
            }
        }
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

    /**
     * @return array of product ids
     */
    public function getProductsNotFound()
    {
        return $this->productsNotFound;
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
    public function offsetSet($index, $newval) {
        throw new LogicException('Attempting to write to an immutable array');
    }

    /**
     * {@inheritdoc}
     *
     * throws LogicException because, it's readonly
     */
    public function offsetUnset($index) {
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