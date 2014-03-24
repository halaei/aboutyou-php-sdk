<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;


class FacetCount extends TermCount
{
    public function getFacet()
    {
        $api = $this->getShopApi();
    }
} 