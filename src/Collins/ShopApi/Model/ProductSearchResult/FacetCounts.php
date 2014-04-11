<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

class FacetCounts extends TermsCounts
{
    protected $groupId;

    public static function createFromJson($groupId, \stdClass $jsonObject)
    {
        $facetCounts = parent::createFromJson($jsonObject);
        $facetCounts->groupId = $groupId;
    }

    public function parseTerms($jsonTerms)
    {
    }
} 