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

    protected function __construct()
    {
    }

    /**
     * @param \stdClass $jsonObject
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject)
    {
        $termCounts = new static();

        $termCounts->productCountTotal                 = $jsonObject->total;
        $termCounts->productCountWithOtherFacetId      = $jsonObject->other;
        $termCounts->productCountWithoutThisFacetGroup = $jsonObject->missing;

        $termCounts->parseTerms($jsonObject->terms);

        return $termCounts;
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