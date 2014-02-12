<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class ProductsResult implements \IteratorAggregate
{
    /** @var Product[] */
    protected $products;

    /** @var string */
    protected $pageHash;

    public function __construct($jsonObject)
    {
        $this->products = [];
        $this->fromJson($jsonObject);
    }

    public function createProduct($jsonProduct)
    {
        return new Product($jsonProduct);
    }

    public function fromJson($jsonObject)
    {
        $this->pageHash = $jsonObject->pageHash;

        foreach ($jsonObject->ids as $key => $jsonProduct) {
            $this->products[$key] = $this->createProduct($jsonProduct);
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
     * allows foreach iteration over the products
     * @return Iterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->products);
    }
}