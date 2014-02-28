<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;


class FacetCount extends TermCount
{
    public function getFacet()
    {
        $api = $this->getShopApi();
    }
} 