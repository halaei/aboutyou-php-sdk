<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class ProductSearchResult extends ProductsResult
{
    /** @var integer */
    protected $productCount;

    /** @var array */
    protected $facets;

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
        $this->productCount = $jsonObject->product_count;

        foreach ($jsonObject->products as $key => $jsonProduct) {
            $this->products[$key] = $this->createProduct($jsonProduct);
        }
    }

    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    public function getFacets()
    {
        return $this->facets;
    }
} 