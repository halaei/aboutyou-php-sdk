<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

class FacetCounts extends TermsCounts
{
    /** @var integer */
    protected $groupId;

    /** @var FacetCount[] */
    protected $facetCounts;

    /**
     * @param integer      $groupId
     * @param \stdClass    $jsonObject
     * @param FacetCount[] $facetCounts
     *
     * @return FacetCounts
     */
    public static function createFromJson($groupId, \stdClass $jsonObject, $facetCounts)
    {
        $self = parent::createFromJson($jsonObject);
        $self->groupId = $groupId;

        $self->facetCounts = $facetCounts;

        return $self;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function getFacetCounts()
    {
        return $this->facetCounts;
    }
}