<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

class ResultError
{
    use ResultErrorTrait;

    public function __construct(\stdClass $jsonObject)
    {
        $this->parseErrorResult($jsonObject);
    }
}