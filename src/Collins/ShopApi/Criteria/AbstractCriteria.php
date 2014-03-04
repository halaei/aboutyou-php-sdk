<?php
namespace Collins\ShopApi\Criteria;

abstract class AbstractCriteria implements \Collins\ShopApi\Criteria\CriteriaInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return array();
    }
}