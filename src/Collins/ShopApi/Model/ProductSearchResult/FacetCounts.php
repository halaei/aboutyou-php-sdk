<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

class FacetCounts extends TermsCounts
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