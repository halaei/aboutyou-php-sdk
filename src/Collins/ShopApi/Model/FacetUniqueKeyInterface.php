<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


interface FacetUniqueKeyInterface
{
    /**
     * @return string
     */
    public function getUniqueKey();
}