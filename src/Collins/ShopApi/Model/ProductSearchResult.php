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


    protected $facets;

    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    public function getFacets()
    {

    }
} 