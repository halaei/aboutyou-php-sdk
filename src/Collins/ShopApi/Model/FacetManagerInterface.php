<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

interface FacetManagerInterface
{
    public function getFacet($groupId, $id);
} 