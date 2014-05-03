<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

interface FetchStrategyInterface
{
    /**
     * @param array $ids this is an array of arrays [<facet group id>=>[<facet id>, <facet id>, ...], ...]
     *
     * @return \Collins\ShopApi\Model\Facet[]
     */
    public function fetch($ids);
} 