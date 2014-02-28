<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


trait ApiResultTrait
{
    protected $jsonObject;

    public function getJson()
    {
        return $this->jsonObject;
    }
} 