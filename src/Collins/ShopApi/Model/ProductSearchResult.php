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

    /** @var array */
    protected $rawFacets;

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
        // workaround for SHOPAPI-278
        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;
        $this->productCount = $jsonObject->product_count;
        $this->rawFacets = $jsonObject->facets;

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

    /**
     * @return array
     */
    public function getRawFacets()
    {
        return $this->rawFacets;
    }

    public function getFacets()
    {
        return $this->facets;
    }
} 