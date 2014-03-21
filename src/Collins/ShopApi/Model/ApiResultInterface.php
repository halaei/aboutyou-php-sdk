<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


interface ApiResultInterface
{
    /**
     * Returns the raw json object
     *
     * @return object
     */
    public function getJson();
} 