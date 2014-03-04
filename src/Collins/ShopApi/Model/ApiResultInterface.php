<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
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