<?php
namespace Collins\ShopApi\Model;

use Collins\ShopApi;

abstract class AbstractResult extends AbstractModel
{
    protected $rawData = null;

    /**
     * Returns json_decoded raw data of the response
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }
}
