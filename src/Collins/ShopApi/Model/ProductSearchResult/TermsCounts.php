<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

abstract class TermsCounts
{
    /** @var integer */
    protected $productCountTotal;

    /** @var integer */
    protected $productCountWithOtherFacet;

    /** @var integer */
    protected $productCountWithoutAnyFacet;

    protected function __construct($productCountTotal, $productCountWithOtherFacet, $productCountWithoutAnyFacet)
    {
        $this->productCountTotal           = $productCountTotal;
        $this->productCountWithOtherFacet  = $productCountWithOtherFacet;
        $this->productCountWithoutAnyFacet = $productCountWithoutAnyFacet;
    }

    /**
     * @return integer
     */
    public function getProductCountTotal()
    {
        return $this->productCountTotal;
    }

    /**
     * @return integer
     */
    public function getProductCountWithOtherFacetId()
    {
        return $this->productCountWithOtherFacet;
    }

    /**
     * @return integer
     */
    public function getProductCountWithoutAnyFacet()
    {
        return $this->productCountWithoutAnyFacet;
    }
}