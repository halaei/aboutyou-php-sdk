<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model;


interface FacetUniqueKeyInterface
{
    /**
     * @return string
     */
    public function getUniqueKey();
}