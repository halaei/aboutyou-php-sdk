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
    protected $productCountWithOtherFacetId;

    /** @var integer */
    protected $productCountWithoutThisFacetGroup;

    /**
     * @param object $jsonObject
     */
    public function __construct($jsonObject)
    {
        $this->productCountTotal                 = $jsonObject->total;
        $this->productCountWithOtherFacetId      = $jsonObject->other;
        $this->productCountWithoutThisFacetGroup = $jsonObject->missing;

        $this->parseTerms($jsonObject->terms);
    }

    /**
     * @return integer
     */
    public function getProductCountTotal()
    {
        return $this->productCountTotal;
    }

    /**
     * @param object $jsonTerms
     */
    abstract protected function parseTerms($jsonTerms);
}