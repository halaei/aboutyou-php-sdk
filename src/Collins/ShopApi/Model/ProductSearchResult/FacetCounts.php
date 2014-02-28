<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

class FacetCounts extends TermsFacet
{
    protected $groupId;

    public function __construct($groupId, $jsonObject)
    {
        $this->groupId = $groupId;
        parent::__construct($jsonObject);
    }

    public function parseTerms($jsonTerms)
    {
    }
} 